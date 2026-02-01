<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\DTO\Input;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateBookmarkInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'The article ID is required.')]
        #[Assert\Uuid(message: 'The article ID must be a valid UUID.')]
        public string $articleId,

        #[Assert\Length(max: 5000, maxMessage: 'Notes cannot exceed 5000 characters.')]
        public ?string $notes = null,
    ) {
    }
}
