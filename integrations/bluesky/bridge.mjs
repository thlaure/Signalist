import { NodeOAuthClient } from '@atproto/oauth-client-node';
import { JoseKey } from '@atproto/jwk-jose';
import { Agent, RichText } from '@atproto/api';
import { TID } from '@atproto/common-web';
import pg from 'pg';
import { secureStore } from './secure-store.mjs';
import { safeFetch, closeFetch } from './safe-fetch.mjs';

let pool;
try {
  let raw = '';
  for await (const chunk of process.stdin) { raw += chunk; if (raw.length > 65536) throw new Error('Input too large'); }
  const input = JSON.parse(raw);
  if (input.action === 'key') { process.stdout.write(JSON.stringify({ rkey: TID.nextStr() })); }
  else {
    const backend = new URL(process.env.SIGNALIST_PUBLIC_API_URL);
    if (backend.protocol !== 'https:') throw new Error('HTTPS required');
    const base = backend.origin;
    const privateKey = process.env.BLUESKY_CLIENT_PRIVATE_KEY ?? '';
    const keyset = [privateKey.startsWith('-----') ? await JoseKey.fromPKCS8(privateKey, 'ES256', 'signalist') : await JoseKey.fromImportable(privateKey, 'signalist')];
    const clientMetadata = {
      client_id: `${base}/oauth/bluesky/client-metadata.json`, client_name: 'Signalist', client_uri: process.env.FRONTEND_URL,
      redirect_uris: [`${base}/api/v1/connections/bluesky/callback`],
      grant_types: ['authorization_code', 'refresh_token'], response_types: ['code'],
      scope: 'atproto transition:generic', application_type: 'web',
      token_endpoint_auth_method: 'private_key_jwt', token_endpoint_auth_signing_alg: 'ES256',
      dpop_bound_access_tokens: true, jwks_uri: `${base}/oauth/bluesky/jwks.json`,
    };
    const metadataOnly = input.action === 'metadata' || input.action === 'jwks';
    if (!metadataOnly && !/^[0-9a-f-]{36}$/i.test(input.ownerId ?? '')) throw new Error('Invalid account');
    const key = Buffer.from(process.env.CREDENTIAL_ENCRYPTION_KEY ?? '', 'base64');
    if (!metadataOnly && key.length !== 32) throw new Error('Encryption key required');
    pool = new pg.Pool({ connectionString: process.env.DATABASE_URL, max: 3, connectionTimeoutMillis: 5000, idleTimeoutMillis: 1000 });
    if (!metadataOnly) {
      const actor = await pool.query('SELECT id FROM "user" WHERE id = $1 AND deleted_at IS NULL AND processing_restricted = false AND email_verified_at IS NOT NULL', [input.ownerId]);
      if (actor.rowCount !== 1) throw new Error('Account unavailable');
    }
    const client = new NodeOAuthClient({
      clientMetadata, keyset, allowHttp: false, fetch: safeFetch,
      stateStore: secureStore(pool, input.ownerId, 'state', key),
      sessionStore: secureStore(pool, input.ownerId, 'session', key),
      async requestLock(lockKey, work) {
        const connection = await pool.connect(); const scopedKey = `bluesky:${input.ownerId}:${lockKey}`;
        try {
          const result = await connection.query('SELECT pg_try_advisory_lock(hashtextextended($1, 0)) AS acquired', [scopedKey]);
          if (!result.rows[0].acquired) throw new Error('Session busy');
          try { return await work(); }
          finally { await connection.query('SELECT pg_advisory_unlock(hashtextextended($1, 0))', [scopedKey]); }
        } finally { connection.release(); }
      },
    });
    let result;
    if (input.action === 'metadata') result = client.clientMetadata;
    else if (input.action === 'jwks') result = client.jwks;
    else if (input.action === 'authorize') {
      if (!/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/i.test(input.handle ?? '')) throw new Error('Valid handle required');
      const url = await client.authorize(input.handle, { state: input.attemptId, signal: AbortSignal.timeout(30_000) });
      result = { url: url.toString() };
    } else if (input.action === 'callback') {
      const { session, state } = await client.callback(new URLSearchParams(input.params));
      if (state !== input.attemptId) { await session.signOut(); throw new Error('State mismatch'); }
      const info = await session.getTokenInfo(false);
      result = { did: session.did, expiresAt: info.expiresAt?.toISOString() ?? new Date(Date.now() + 3600_000).toISOString() };
    } else if (input.action === 'post') {
      const session = await client.restore(input.did);
      const text = new RichText({ text: input.text });
      if (text.graphemeLength > 300 || Buffer.byteLength(input.text) > 3000) throw new Error('Post too long');
      await text.detectFacetsWithoutResolution();
      const agent = new Agent(session);
      const resultRecord = await agent.com.atproto.repo.putRecord({ repo: session.did, collection: 'app.bsky.feed.post', rkey: input.rkey,
        record: { $type: 'app.bsky.feed.post', text: text.text, facets: text.facets?.filter((facet) => !facet.features.some((feature) => feature.$type === 'app.bsky.richtext.facet#mention')), createdAt: input.createdAt }, validate: true });
      result = { uri: resultRecord.data.uri };
    } else if (input.action === 'revoke') {
      const session = await client.restore(input.did); await session.signOut(); result = { revoked: true };
    } else throw new Error('Unsupported action');
    process.stdout.write(JSON.stringify(result));
  }
} catch {
  process.stdout.write(JSON.stringify({ error: 'Bluesky request failed.' }));
  process.exitCode = 1;
} finally {
  await pool?.end();
  await closeFetch();
}
