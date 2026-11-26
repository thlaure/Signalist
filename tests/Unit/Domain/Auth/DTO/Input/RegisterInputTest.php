<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\DTO\Input;

use App\Domain\Auth\DTO\Input\RegisterInput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class RegisterInputTest extends TestCase
{
    #[DataProvider('passwordCases')]
    public function testPasswordRequiresLengthWithoutComplexityScore(string $password, bool $valid): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate(new RegisterInput('fixture@example.test', $password));

        $this->assertSame($valid, 0 === $violations->count());
    }

    /** @return iterable<string, array{string, bool}> */
    public static function passwordCases(): iterable
    {
        yield 'eight lowercase characters' => ['abcdefgh', true];
        yield 'eight digits' => ['12345678', true];
        yield 'repeated block' => ['abcdefghabcdefgh', true];
        yield 'passphrase' => ['a synthetic test passphrase', true];
        yield 'seven characters' => ['abcdefg', false];
        yield 'empty' => ['', false];
    }

    public function testInvalidEmailStillFailsValidation(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate(new RegisterInput('invalid-email', 'abcdefgh'));

        $this->assertGreaterThan(0, $violations->count());
        $this->assertSame('email', $violations->get(0)->getPropertyPath());
    }
}
