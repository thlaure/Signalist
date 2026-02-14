<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Handler\ListCategoriesHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListCategoriesHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private ListCategoriesHandler $handler;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new ListCategoriesHandler($this->categoryRepository);
    }

    public function testInvokeReturnsAllCategories(): void
    {
        $categories = [
            $this->createMock(Category::class),
            $this->createMock(Category::class),
        ];

        $this->categoryRepository
            ->method('findAll')
            ->willReturn($categories);

        $result = ($this->handler)();

        $this->assertCount(2, $result);
        $this->assertSame($categories, $result);
    }

    public function testInvokeNoCategoriesExistReturnsEmptyArray(): void
    {
        $this->categoryRepository
            ->method('findAll')
            ->willReturn([]);

        $result = ($this->handler)();

        $this->assertSame([], $result);
    }
}
