<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use function is_string;

use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

use function strlen;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class TokenCipher
{
    private ?string $key;

    public function __construct(string $encodedKey)
    {
        $decoded = base64_decode($encodedKey, true);
        $this->key = is_string($decoded) && SODIUM_CRYPTO_SECRETBOX_KEYBYTES === strlen($decoded) ? $decoded : null;
    }

    public function configured(): bool
    {
        return null !== $this->key;
    }

    public function encrypt(string $value): string
    {
        if (null === $this->key) {
            throw new UnrecoverableMessageHandlingException('Credential encryption setup is pending.');
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return base64_encode($nonce.sodium_crypto_secretbox($value, $nonce, $this->key));
    }

    public function decrypt(string $value): string
    {
        if (null === $this->key) {
            throw new UnrecoverableMessageHandlingException('Credential encryption setup is pending.');
        }
        $decoded = base64_decode($value, true);

        if (false === $decoded || strlen($decoded) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new UnrecoverableMessageHandlingException('Stored credentials are invalid.');
        }
        $plaintext = sodium_crypto_secretbox_open(substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), $this->key);

        if (false === $plaintext) {
            throw new UnrecoverableMessageHandlingException('Stored credentials are invalid.');
        }

        return $plaintext;
    }
}
