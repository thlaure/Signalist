<?php

declare(strict_types=1);

namespace App\Domain\AI\Service;

use DOMDocument;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;

use const LIBXML_NOERROR;
use const LIBXML_NONET;
use const LIBXML_NOWARNING;

final readonly class ArticleTextCleaner
{
    public function clean(string $html): string
    {
        if ('' === trim($html)) {
            return '';
        }
        $previous = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument();
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $nodes = new DOMXPath($document)->query('//script|//style|//nav|//footer|//noscript|//form|//iframe');

            if (false !== $nodes) {
                foreach ($nodes as $node) {
                    if ($node instanceof DOMNode) {
                        $node->parentNode?->removeChild($node);
                    }
                }
            }
            $blocks = new DOMXPath($document)->query('//p|//h1|//h2|//h3|//li|//br');

            if (false !== $blocks) {
                foreach ($blocks as $block) {
                    if ($block instanceof DOMNode) {
                        $block->appendChild($document->createTextNode(' '));
                    }
                }
            }

            return trim(preg_replace('/\s+/u', ' ', $document->textContent) ?? '');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /** @return list<string> */
    public function chunks(string $text, int $maxCharacters = 3000): array
    {
        if ($maxCharacters < 100) {
            throw new InvalidArgumentException('Chunk size must be at least 100 characters.');
        }
        $chunks = [];
        $text = trim($text);
        while (mb_strlen($text) > $maxCharacters) {
            $candidate = mb_substr($text, 0, $maxCharacters);
            $boundary = mb_strrpos($candidate, ' ');
            $length = false === $boundary || $boundary < $maxCharacters / 2 ? $maxCharacters : $boundary;
            $chunks[] = mb_substr($text, 0, $length);
            $text = ltrim(mb_substr($text, $length));
        }

        if ('' !== $text) {
            $chunks[] = $text;
        }

        return $chunks;
    }
}
