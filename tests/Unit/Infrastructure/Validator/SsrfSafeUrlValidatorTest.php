<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Validator;

use App\Infrastructure\Validator\SsrfSafeUrl;
use App\Infrastructure\Validator\SsrfSafeUrlValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class SsrfSafeUrlValidatorTest extends TestCase
{
    private SsrfSafeUrlValidator $validator;

    private ExecutionContext&MockObject $context;

    protected function setUp(): void
    {
        $this->validator = new SsrfSafeUrlValidator();
        $this->context = $this->createMock(ExecutionContext::class);
        $this->validator->initialize($this->context);
    }

    public function testValidateWithNullValueDoesNothing(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(null, new SsrfSafeUrl());
    }

    public function testValidateWithEmptyStringDoesNothing(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate('', new SsrfSafeUrl());
    }

    public function testValidateWithPublicHttpsUrlAddsNoViolation(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        // Use a public IP directly to avoid DNS resolution in test environment
        $this->validator->validate('https://8.8.8.8/rss', new SsrfSafeUrl());
    }

    public function testValidateWithPublicHttpUrlAddsNoViolation(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        // Use a public IP directly to avoid DNS resolution in test environment
        $this->validator->validate('http://1.1.1.1/rss', new SsrfSafeUrl());
    }

    public function testValidateWithFileSchemeAddsViolation(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate('file:///etc/passwd', new SsrfSafeUrl());
    }

    public function testValidateWithLocalhostAddsViolation(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate('http://127.0.0.1/internal', new SsrfSafeUrl());
    }

    public function testValidateWithPrivateIpRangeAddsViolation(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate('http://192.168.1.1/feed', new SsrfSafeUrl());
    }

    public function testValidateWithCloudMetadataIpAddsViolation(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate('http://169.254.169.254/latest/meta-data/', new SsrfSafeUrl());
    }

    public function testValidateWithInternalNetworkRangeAddsViolation(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate('http://10.0.0.1/feed', new SsrfSafeUrl());
    }
}
