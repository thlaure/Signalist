<?php

declare(strict_types=1);

namespace App\Domain\Digest\DTO;

use App\Infrastructure\Validator\SsrfSafeUrl;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SourceInput
{
    public function __construct(
        #[Assert\NotBlank] #[Assert\Url(protocols: ['https', 'http'], requireTld: false)] #[Assert\Length(max: 2048)] #[SsrfSafeUrl] public string $url,
    ) {
    }
}
