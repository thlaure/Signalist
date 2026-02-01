<?php

declare(strict_types=1);

namespace App\Domain\Feed\DTO\Input;

use App\Entity\Feed;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateFeedInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'The feed title is required.')]
        #[Assert\Length(max: 255, maxMessage: 'The title cannot exceed 255 characters.')]
        public string $title,

        #[Assert\NotBlank(message: 'The category ID is required.')]
        #[Assert\Uuid(message: 'The category ID must be a valid UUID.')]
        public string $categoryId,

        #[Assert\NotBlank(message: 'The status is required.')]
        #[Assert\Choice(
            choices: [Feed::STATUS_ACTIVE, Feed::STATUS_PAUSED, Feed::STATUS_ERROR],
            message: 'The status must be one of: active, paused, error.',
        )]
        public string $status = Feed::STATUS_ACTIVE,
    ) {
    }
}
