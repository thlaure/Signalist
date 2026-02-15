<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Handler\GetCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Category\Query\GetCategoryQuery;
use App\Entity\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private GetCategoryHandler $handler;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new GetCategoryHandler($this->categoryRepository);
    }

    public function testInvokeExistingCategoryReturnsCategory(): void
    {
        $categoryId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $category = $this->createMock(Category::class);

        $this->categoryRepository
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $result = ($this->handler)(new GetCategoryQuery($categoryId));

        $this->assertSame($category, $result);
    }

    public function testInvokeNonExistentCategoryThrowsCategoryNotFoundException(): void
    {
        $this->categoryRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        ($this->handler)(new GetCategoryQuery('non-existent-id'));
    }
}
