<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Exception\CategorySlugAlreadyExistsException;
use App\Domain\Category\Handler\CreateCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private UserRepositoryInterface&MockObject $userRepository;

    private CreateCategoryHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new CreateCategoryHandler($this->categoryRepository, $this->userRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeValidDataSavesCategoryAndReturnsUuid(): void
    {
        $user = $this->createMock(User::class);

        $this->userRepository
            ->method('find')
            ->with($this->ownerId)
            ->willReturn($user);

        $this->categoryRepository
            ->method('findBySlugAndOwner')
            ->with('technology', $this->ownerId)
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save');

        $result = ($this->handler)(new CreateCategoryCommand(
            name: 'Technology',
            slug: 'technology',
            ownerId: $this->ownerId,
            description: 'Tech news',
            color: '#3498db',
            position: 1,
        ));

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testInvokeDuplicateSlugThrowsCategorySlugAlreadyExistsException(): void
    {
        $user = $this->createMock(User::class);
        $existing = $this->createMock(Category::class);

        $this->userRepository->method('find')->willReturn($user);

        $this->categoryRepository
            ->method('findBySlugAndOwner')
            ->with('tech', $this->ownerId)
            ->willReturn($existing);

        $this->expectException(CategorySlugAlreadyExistsException::class);

        ($this->handler)(new CreateCategoryCommand(
            name: 'Technology',
            slug: 'tech',
            ownerId: $this->ownerId,
        ));
    }
}
