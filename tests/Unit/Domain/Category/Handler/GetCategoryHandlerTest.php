<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Handler\GetCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Category\Query\GetCategoryQuery;
use App\Entity\Category;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private GetCategoryHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new GetCategoryHandler($this->categoryRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeExistingCategoryReturnsCategory(): void
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

        $result = ($this->handler)(new GetCategoryQuery($categoryId, $this->ownerId));

        $this->assertSame($category, $result);
    }

    public function testInvokeNonExistentCategoryThrowsCategoryNotFoundException(): void
    {
        $this->categoryRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        ($this->handler)(new GetCategoryQuery('non-existent-id', $this->ownerId));
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

        ($this->handler)(new GetCategoryQuery($categoryId, $this->ownerId));
    }
}
