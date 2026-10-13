<?php

declare(strict_types=1);

namespace App\Domain\Tag\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ArticleTagsInput
{
    /** @param list<string> $tags */
    public function __construct(#[Assert\Count(max: 20)] #[Assert\All([new Assert\Type('string'), new Assert\NotBlank(), new Assert\Length(max: 60)])] public array $tags)
    {
    }
}
