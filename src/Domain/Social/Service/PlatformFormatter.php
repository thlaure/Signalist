<?php

declare(strict_types=1);

namespace App\Domain\Social\Service;

use function count;

use InvalidArgumentException;

use const PREG_SPLIT_NO_EMPTY;

use function strlen;

final readonly class PlatformFormatter
{
    /** @return list<string> */
    public function format(string $platform, string $text): array
    {
        $limit = match ($platform) {
            'linkedin' => 3000, 'instagram' => 2200, 'x' => 260, 'bluesky' => 300, default => throw new InvalidArgumentException('Unsupported platform.')
        };
        $text = trim(strip_tags($text));

        if ('' === $text) {
            throw new InvalidArgumentException('Post content is empty.');
        }

        if ('x' !== $platform) {
            if ($this->length($platform, $text) > $limit || ('bluesky' === $platform && strlen($text) > 3000)) {
                throw new InvalidArgumentException('Post exceeds platform length. Edit the draft first.');
            }

            return [$text];
        }
        $posts = [];
        $current = '';
        foreach (preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
            if ($this->length('x', $word) > $limit) {
                throw new InvalidArgumentException('A word exceeds the thread length limit.');
            }
            $candidate = '' === $current ? $word : $current.' '.$word;

            if ($this->length('x', $candidate) > $limit) {
                $posts[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ('' !== $current) {
            $posts[] = $current;
        }

        if (count($posts) > 20) {
            throw new InvalidArgumentException('Thread exceeds 20 posts.');
        }

        if (count($posts) > 1) {
            $total = count($posts);
            foreach ($posts as $index => &$post) {
                $post .= ' '.($index + 1).'/'.$total;
            } unset($post);
        }

        return $posts;
    }

    public function length(string $platform, string $text): int
    {
        if ('bluesky' === $platform) {
            return grapheme_strlen($text) ?: 0;
        }

        if ('x' !== $platform) {
            return mb_strlen($text);
        }
        // Conservative Unicode weighting: at most two units per code point, URLs fixed at 23.
        $urls = 0;
        $plain = preg_replace_callback('~https?://[^\s]+~u', static function () use (&$urls): string {
            ++$urls;

            return '';
        }, $text) ?? $text;

        return mb_strlen($plain) * 2 + $urls * 23;
    }
}
