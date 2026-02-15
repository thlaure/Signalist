<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\DTO\Input;

use App\Domain\Auth\DTO\Input\LoginInput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LoginInputTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidInputPassesValidation(): void
    {
        $input = new LoginInput(
            email: 'admin@signalist.app',
            password: 'password123',
        );

        $violations = $this->validator->validate($input);

        $this->assertCount(0, $violations);
    }

    public function testBlankEmailFailsValidation(): void
    {
        $input = new LoginInput(
            email: '',
            password: 'password123',
        );

        $violations = $this->validator->validate($input);

        $this->assertGreaterThan(0, $violations->count());
        $this->assertSame('email', $violations->get(0)->getPropertyPath());
    }

    public function testInvalidEmailFormatFailsValidation(): void
    {
        $input = new LoginInput(
            email: 'not-an-email',
            password: 'password123',
        );

        $violations = $this->validator->validate($input);

        $this->assertGreaterThan(0, $violations->count());
        $this->assertSame('email', $violations->get(0)->getPropertyPath());
    }

    public function testBlankPasswordFailsValidation(): void
    {
        $input = new LoginInput(
            email: 'admin@signalist.app',
            password: '',
        );

        $violations = $this->validator->validate($input);

        $this->assertGreaterThan(0, $violations->count());

        $paths = [];
        for ($i = 0; $i < $violations->count(); ++$i) {
            $paths[] = $violations->get($i)->getPropertyPath();
        }

        $this->assertContains('password', $paths);
    }

    public function testPasswordShorterThan8CharactersFailsValidation(): void
    {
        $input = new LoginInput(
            email: 'admin@signalist.app',
            password: 'short',
        );

        $violations = $this->validator->validate($input);

        $this->assertGreaterThan(0, $violations->count());
        $this->assertSame('password', $violations->get(0)->getPropertyPath());
    }
}
