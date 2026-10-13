<?php

declare(strict_types=1);

namespace App\Domain\AI\Service;

final readonly class ArticleAnonymizer
{
    /** @param list<string> $names Explicit names identified by the source or account; arbitrary name detection needs a dedicated provider. */
    public function anonymize(string $text, array $names = []): string
    {
        foreach ($names as $name) {
            if ('' !== trim($name)) {
                $text = str_ireplace($name, '[PERSON]', $text);
            }
        }
        $patterns = [
            '/[\p{L}\p{N}._%+\-]+@[\p{L}\p{N}.\-]+\.[\p{L}]{2,}/u' => '[EMAIL]',
            '/(?<![\w.])(?:\d{1,3}\.){3}\d{1,3}(?![\w.])/' => '[IP]',
            '/(?<![\w])(?:[a-f0-9]{0,4}:){2,}[a-f0-9:]{0,39}(?![\w])/i' => '[IP]',
            '/(?<!\w)\+?\d[\d\s().-]{7,}\d(?!\w)/' => '[PHONE]',
        ];
        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? '';
        }

        return $text;
    }
}
