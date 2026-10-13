import { createCipheriv, createDecipheriv, randomBytes, randomUUID } from 'node:crypto';

export function encrypt(value, key, context) {
  const nonce = randomBytes(12);
  const cipher = createCipheriv('aes-256-gcm', key, nonce);
  cipher.setAAD(Buffer.from(context));
  const ciphertext = Buffer.concat([cipher.update(JSON.stringify(value), 'utf8'), cipher.final()]);
  return Buffer.concat([nonce, cipher.getAuthTag(), ciphertext]).toString('base64');
}

export function decrypt(value, key, context) {
  const bytes = Buffer.from(value, 'base64');
  if (bytes.length < 29) throw new Error('Invalid stored session');
  const decipher = createDecipheriv('aes-256-gcm', key, bytes.subarray(0, 12));
  decipher.setAAD(Buffer.from(context));
  decipher.setAuthTag(bytes.subarray(12, 28));
  return JSON.parse(Buffer.concat([decipher.update(bytes.subarray(28)), decipher.final()]).toString('utf8'));
}

export function secureStore(pool, ownerId, kind, key) {
  return {
    async set(storeKey, value) {
      const context = `${ownerId}|${kind}|${storeKey}`;
      const expires = new Date(Date.now() + (kind === 'state' ? 600_000 : 30 * 86400_000));
      const result = await pool.query(`
        INSERT INTO oauth_sdk_store (id, owner_id, kind, store_key, encrypted_value, expires_at)
        SELECT $1, id, $2, $3, $4, $5 FROM "user" WHERE id = $6 AND deleted_at IS NULL AND processing_restricted = false
        ON CONFLICT (owner_id, kind, store_key) DO UPDATE SET encrypted_value = EXCLUDED.encrypted_value, expires_at = EXCLUDED.expires_at
      `, [randomUUID(), kind, storeKey, encrypt(value, key, context), expires, ownerId]);
      if (result.rowCount !== 1) throw new Error('Account unavailable');
    },
    async get(storeKey) {
      const result = await pool.query('SELECT encrypted_value FROM oauth_sdk_store WHERE owner_id = $1 AND kind = $2 AND store_key = $3 AND expires_at > CURRENT_TIMESTAMP', [ownerId, kind, storeKey]);
      return result.rows.length ? decrypt(result.rows[0].encrypted_value, key, `${ownerId}|${kind}|${storeKey}`) : undefined;
    },
    async del(storeKey) {
      await pool.query('DELETE FROM oauth_sdk_store WHERE owner_id = $1 AND kind = $2 AND store_key = $3', [ownerId, kind, storeKey]);
    },
  };
}
