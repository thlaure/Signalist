<?php

declare(strict_types=1);

namespace App\Domain\Spotlight\Service;

final readonly class CommandParser
{
    /** @return array{intent: string, value: string, category?: string} */
    public function parse(string $command): array
    {
        $command = trim($command);

        if (preg_match('/^(?:create category|créer catégorie)\s+(.+)$/iu', $command, $matches)) {
            return ['intent' => 'create_category', 'value' => trim($matches[1])];
        }

        if (preg_match('/^(?:add feed|ajouter flux)\s+(https?:\/\/\S+)\s+(?:in|dans)\s+(.+)$/iu', $command, $matches)) {
            return ['intent' => 'add_feed', 'value' => $matches[1], 'category' => trim($matches[2])];
        }

        if (preg_match('/^(?:ask|demander)\s+(.+)$/iu', $command, $matches)) {
            return ['intent' => 'question', 'value' => trim($matches[1])];
        }

        return ['intent' => 'search', 'value' => $command];
    }
}
