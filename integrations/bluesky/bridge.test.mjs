import test from 'node:test';
import assert from 'node:assert/strict';
import { generateKeyPairSync } from 'node:crypto';
import { spawnSync } from 'node:child_process';

function bridge(input, env = {}) {
  return spawnSync(process.execPath, ['bridge.mjs'], { cwd: import.meta.dirname, input: JSON.stringify(input), encoding: 'utf8', timeout: 10000, env: { PATH: process.env.PATH, NODE_TLS_REJECT_UNAUTHORIZED: '1', ...env } });
}
test('SDK metadata exposes only public signing material and does not connect to the database', () => {
  const { privateKey } = generateKeyPairSync('ec', { namedCurve: 'prime256v1' });
  const env = { SIGNALIST_PUBLIC_API_URL: 'https://api.example.com', FRONTEND_URL: 'https://app.example.com', BLUESKY_CLIENT_PRIVATE_KEY: privateKey.export({ type: 'pkcs8', format: 'pem' }), DATABASE_URL: 'postgresql://invalid:invalid@127.0.0.1:1/invalid' };
  const metadata = bridge({ action: 'metadata' }, env);
  assert.equal(metadata.status, 0, metadata.stdout);
  const data = JSON.parse(metadata.stdout);
  assert.equal(data.client_id, 'https://api.example.com/oauth/bluesky/client-metadata.json');
  assert.equal(data.dpop_bound_access_tokens, true);
  assert.equal(data.token_endpoint_auth_method, 'private_key_jwt');
  const jwks = bridge({ action: 'jwks' }, env);
  assert.equal(jwks.status, 0, jwks.stdout);
  const keys = JSON.parse(jwks.stdout).keys;
  assert.equal(keys.length, 1);
  assert.equal(keys[0].kty, 'EC');
  for (const field of ['d', 'p', 'q', 'dp', 'dq', 'qi']) assert.equal(keys[0][field], undefined);
  assert.equal(jwks.stdout.includes('PRIVATE KEY'), false);
});
test('record keys are SDK-generated TIDs without needing credentials', () => {
  const result = bridge({ action: 'key' });
  assert.equal(result.status, 0);
  assert.match(JSON.parse(result.stdout).rkey, /^[234567abcdefghijklmnopqrstuvwxyz]{13}$/);
});
test('failures expose a fixed message without credential material', () => {
  const result = bridge({ action: 'post', text: 'private-text', ownerId: 'invalid' }, { SIGNALIST_PUBLIC_API_URL: 'http://localhost', BLUESKY_CLIENT_PRIVATE_KEY: 'private-secret' });
  assert.equal(result.status, 1);
  assert.deepEqual(JSON.parse(result.stdout), { error: 'Bluesky request failed.' });
  assert.equal(result.stdout.includes('private-secret'), false);
});
