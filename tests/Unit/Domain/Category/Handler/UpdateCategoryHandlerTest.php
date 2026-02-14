<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Command\UpdateCategoryCommand;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Exception\CategorySlugAlreadyExistsException;
use App\Domain\Category\Handler\UpdateCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private UpdateCategoryHandler $handler;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new UpdateCategoryHandler($this->categoryRepository);
    }

    public function testInvokeValidUpdateSavesCategory(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $category = $this->createMock(Category::class);

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('findBySlug')
            ->with('new-slug')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save');

        ($this->handler)(new UpdateCategoryCommand(
            id: $categoryId,
            name: 'New Name',
            slug: 'new-slug',
        ));
    }

    public function testInvokeSameSlugSameCategorySavesSuccessfully(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();

        $category = $this->createMock(Category::class);
        $existingWithSlug = $this->createMock(Category::class);
        $existingWithSlug->method('getId')->willReturn(Uuid::fromString($categoryId));

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('findBySlug')
            ->with('same-slug')
            ->willReturn($existingWithSlug);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save');

        ($this->handler)(new UpdateCategoryCommand(
            id: $categoryId,
            name: 'Updated Name',
            slug: 'same-slug',
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
        ));
    }

    public function testInvokeDuplicateSlugDifferentCategoryThrowsCategorySlugAlreadyExistsException(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $otherId = Uuid::v7()->toRfc4122();

        $category = $this->createMock(Category::class);
        $existingWithSlug = $this->createMock(Category::class);
        $existingWithSlug->method('getId')->willReturn(Uuid::fromString($otherId));

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('findBySlug')
            ->with('taken-slug')
            ->willReturn($existingWithSlug);

        $this->expectException(CategorySlugAlreadyExistsException::class);

        ($this->handler)(new UpdateCategoryCommand(
            id: $categoryId,
            name: 'Name',
            slug: 'taken-slug',
        ));
    }
}
