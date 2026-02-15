<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Command\UpdateCategoryCommand;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Exception\CategorySlugAlreadyExistsException;
use App\Domain\Category\Handler\UpdateCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private UpdateCategoryHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new UpdateCategoryHandler($this->categoryRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeValidUpdateSavesCategory(): void
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
            ->method('findBySlugAndOwner')
            ->with('new-slug', $this->ownerId)
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save');

        ($this->handler)(new UpdateCategoryCommand(
            id: $categoryId,
            name: 'New Name',
            slug: 'new-slug',
            ownerId: $this->ownerId,
        ));
    }

    public function testInvokeSameSlugSameCategorySavesSuccessfully(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();

        $category = $this->createMock(Category::class);
        $existingWithSlug = $this->createMock(Category::class);
        $existingWithSlug->method('getId')->willReturn(Uuid::fromString($categoryId));

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $category->method('getOwner')->willReturn($owner);

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('findBySlugAndOwner')
            ->with('same-slug', $this->ownerId)
            ->willReturn($existingWithSlug);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save');

        ($this->handler)(new UpdateCategoryCommand(
            id: $categoryId,
            name: 'Updated Name',
            slug: 'same-slug',
            ownerId: $this->ownerId,
        ));
    }

    public function testInvokeNonExistentCategoryThrowsCategoryNotFoundException(): void
    {
        $this->categoryRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        ($this->handler)(new UpdateCategoryCommand(
            id: 'non-existent-id',
            name: 'Name',
            slug: 'slug',
            ownerId: $this->ownerId,
        ));
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

        ($this->handler)(new UpdateCategoryCommand(
            id: $categoryId,
            name: 'Name',
            slug: 'slug',
            ownerId: $this->ownerId,
        ));
    }

    public function testInvokeDuplicateSlugDifferentCategoryThrowsCategorySlugAlreadyExistsException(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $otherId = Uuid::v7()->toRfc4122();

        $category = $this->createMock(Category::class);
        $existingWithSlug = $this->createMock(Category::class);
        $existingWithSlug->method('getId')->willReturn(Uuid::fromString($otherId));

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $category->method('getOwner')->willReturn($owner);

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('findBySlugAndOwner')
            ->with('taken-slug', $this->ownerId)
            ->willReturn($existingWithSlug);

        $this->expectException(CategorySlugAlreadyExistsException::class);

        ($this->handler)(new UpdateCategoryCommand(
            id: $categoryId,
            name: 'Name',
            slug: 'taken-slug',
            ownerId: $this->ownerId,
        ));
    }
}
