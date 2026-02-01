<?php

declare(strict_types=1);

namespace App\Domain\Feed\DTO\Input;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AddFeedInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'The feed URL is required.')]
        #[Assert\Url(message: 'The feed URL must be a valid URL.')]
        public string $url,

        #[Assert\NotBlank(message: 'The category ID is required.')]
        #[Assert\Uuid(message: 'The category ID must be a valid UUID.')]
        public string $categoryId,

        #[Assert\Length(max: 255, maxMessage: 'The title cannot exceed 255 characters.')]
        public ?string $title = null,
    ) {
    }
}
