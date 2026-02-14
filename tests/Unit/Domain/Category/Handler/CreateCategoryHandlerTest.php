<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Handler;

use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Exception\CategorySlugAlreadyExistsException;
use App\Domain\Category\Handler\CreateCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private CreateCategoryHandler $handler;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new CreateCategoryHandler($this->categoryRepository);
    }

    public function testInvokeValidDataSavesCategoryAndReturnsUuid(): void
    {
        $this->categoryRepository
            ->method('findBySlug')
            ->with('technology')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save');

        $result = ($this->handler)(new CreateCategoryCommand(
            name: 'Technology',
            slug: 'technology',
            description: 'Tech news',
            color: '#3498db',
            position: 1,
        ));

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testInvokeDuplicateSlugThrowsCategorySlugAlreadyExistsException(): void
    {
        $existing = $this->createMock(Category::class);

        $this->categoryRepository
            ->method('findBySlug')
            ->with('tech')
            ->willReturn($existing);

        $this->expectException(CategorySlugAlreadyExistsException::class);

        ($this->handler)(new CreateCategoryCommand(
            name: 'Technology',
            slug: 'tech',
        ));
    }
}
