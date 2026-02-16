<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Handler\ListCategoriesHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Category\Query\ListCategoriesQuery;
use App\Entity\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListCategoriesHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private ListCategoriesHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new ListCategoriesHandler($this->categoryRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeReturnsAllCategories(): void
    {
        $categories = [
            $this->createMock(Category::class),
            $this->createMock(Category::class),
        ];

        $this->categoryRepository
            ->method('findAllByOwner')
            ->with($this->ownerId)
            ->willReturn($categories);

        $result = ($this->handler)(new ListCategoriesQuery($this->ownerId));

        $this->assertCount(2, $result);
        $this->assertSame($categories, $result);
    }

    public function testInvokeNoCategoriesExistReturnsEmptyArray(): void
    {
        $this->categoryRepository
            ->method('findAllByOwner')
            ->with($this->ownerId)
            ->willReturn([]);

        $result = ($this->handler)(new ListCategoriesQuery($this->ownerId));

        $this->assertSame([], $result);
    }
}
