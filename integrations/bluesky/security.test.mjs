import { test } from 'node:test';
import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { encrypt, decrypt } from './secure-store.mjs';
import { publicAddress, safeTarget, closeFetch } from './safe-fetch.mjs';

test('session encryption rejects tampering and cross-account ciphertext reuse', () => {
  const key = randomBytes(32); const payload = { access_token: 'fixture-token', privateKey: 'fixture-key' };
  const encrypted = encrypt(payload, key, 'owner-a|session|did:plc:fixture');
  assert.deepEqual(decrypt(encrypted, key, 'owner-a|session|did:plc:fixture'), payload);
  assert.throws(() => decrypt(encrypted, key, 'owner-b|session|did:plc:fixture'));
  assert.throws(() => decrypt(encrypted, randomBytes(32), 'owner-a|session|did:plc:fixture'));
  const changed = Buffer.from(encrypted, 'base64'); changed[changed.length - 1] ^= 1;
  assert.throws(() => decrypt(changed.toString('base64'), key, 'owner-a|session|did:plc:fixture'));
});

test('discovery rejects private, mapped, reserved addresses and unsafe URL schemes', () => {
  for (const address of ['127.0.0.1', '10.1.2.3', '169.254.169.254', '100.64.0.1', '::1', '::ffff:127.0.0.1', 'fe80::1', '2001:db8::1']) assert.equal(publicAddress(address), false);
  assert.equal(publicAddress('8.8.8.8'), true);
  assert.equal(publicAddress('2606:4700:4700::1111'), true);
  for (const url of ['http://example.com', 'https://127.0.0.1', 'https://[::1]', 'https://user:secret@example.com', 'https://example.com:8443', 'file:///etc/passwd']) assert.throws(() => safeTarget(url));
  assert.equal(safeTarget('https://bsky.social/oauth/token').hostname, 'bsky.social');
});

test.after(async () => { await closeFetch(); });
