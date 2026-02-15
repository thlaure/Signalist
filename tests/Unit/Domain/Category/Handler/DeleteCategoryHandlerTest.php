<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Command\DeleteCategoryCommand;
use App\Domain\Category\Exception\CategoryHasFeedsException;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Handler\DeleteCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DeleteCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private DeleteCategoryHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new DeleteCategoryHandler($this->categoryRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeExistingCategoryWithoutFeedsDeletesCategory(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $category = $this->createMock(Category::class);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $category->method('getOwner')->willReturn($owner);

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('hasFeedsAssigned')
            ->with($categoryId)
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('delete')
            ->with($category);

        ($this->handler)(new DeleteCategoryCommand($categoryId, $this->ownerId));
    }

    public function testInvokeNonExistentCategoryThrowsCategoryNotFoundException(): void
    {
        $this->categoryRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        ($this->handler)(new DeleteCategoryCommand('non-existent-id', $this->ownerId));
    }

    public function testInvokeCategoryOwnedByDifferentUserThrowsCategoryNotFoundException(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $category = $this->createMock(Category::class);

        $otherOwner = $this->createMock(User::class);
        $otherOwner->method('getId')->willReturn(Uuid::v7());
        $category->method('getOwner')->willReturn($otherOwner);

        $this->categoryRepository->method('find')->willReturn($category);

        $this->expectException(CategoryNotFoundException::class);

        ($this->handler)(new DeleteCategoryCommand($categoryId, $this->ownerId));
    }

    public function testInvokeCategoryWithFeedsThrowsCategoryHasFeedsException(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $category = $this->createMock(Category::class);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $category->method('getOwner')->willReturn($owner);

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('hasFeedsAssigned')
            ->with($categoryId)
            ->willReturn(true);

        $this->expectException(CategoryHasFeedsException::class);

        ($this->handler)(new DeleteCategoryCommand($categoryId, $this->ownerId));
    }
}
