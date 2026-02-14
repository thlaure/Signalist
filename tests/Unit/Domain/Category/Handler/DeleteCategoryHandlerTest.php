<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Command\DeleteCategoryCommand;
use App\Domain\Category\Exception\CategoryHasFeedsException;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Handler\DeleteCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private DeleteCategoryHandler $handler;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new DeleteCategoryHandler($this->categoryRepository);
    }

    public function testInvokeExistingCategoryWithoutFeedsDeletesCategory(): void
    {
        $categoryId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $category = $this->createMock(Category::class);

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

        ($this->handler)(new DeleteCategoryCommand($categoryId));
    }

    public function testInvokeNonExistentCategoryThrowsCategoryNotFoundException(): void
    {
        $this->categoryRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        ($this->handler)(new DeleteCategoryCommand('non-existent-id'));
    }

    public function testInvokeCategoryWithFeedsThrowsCategoryHasFeedsException(): void
    {
        $categoryId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $category = $this->createMock(Category::class);

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->method('hasFeedsAssigned')
            ->with($categoryId)
            ->willReturn(true);

        $this->expectException(CategoryHasFeedsException::class);

        ($this->handler)(new DeleteCategoryCommand($categoryId));
    }
}
