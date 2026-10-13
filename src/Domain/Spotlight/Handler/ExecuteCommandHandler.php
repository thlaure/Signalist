<?php

declare(strict_types=1);

namespace App\Domain\Spotlight\Handler;

use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\DTO\Input\CreateCategoryInput;
use App\Domain\Category\Handler\CreateCategoryHandler;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Command\AddFeedCommand;
use App\Domain\Feed\DTO\Input\AddFeedInput;
use App\Domain\Feed\Handler\AddFeedHandler;
use App\Domain\Spotlight\Service\CommandParser;
use App\Entity\User;
use App\Infrastructure\Exception\ValidationException;

use function count;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ExecuteCommandHandler
{
    public function __construct(private CommandParser $parser, private CreateCategoryHandler $createCategory, private AddFeedHandler $addFeed, private CategoryRepositoryInterface $categories, private ValidatorInterface $validator)
    {
    }

    public function execute(User $user, string $command): string
    {
        $parsed = $this->parser->parse($command);
        $ownerId = $user->getId()->toRfc4122();

        if ('question' === $parsed['intent']) {
            return '/search?mode=question&q='.rawurlencode($parsed['value']);
        }

        if ('search' === $parsed['intent']) {
            return '/search?q='.rawurlencode($parsed['value']);
        }

        if ($user->isProcessingRestricted()) {
            throw new AccessDeniedException('Processing is restricted.');
        }

        if ('create_category' === $parsed['intent']) {
            $slug = new AsciiSlugger('en')->slug($parsed['value'])->lower()->toString();
            $input = new CreateCategoryInput($parsed['value'], $slug);
            $this->validate($input);
            $id = ($this->createCategory)(new CreateCategoryCommand($input->name, $input->slug, $ownerId));

            return '/categories/'.$id;
        }
        $matches = [];
        foreach ($this->categories->findAllByOwner($ownerId) as $category) {
            if (mb_strtolower($category->getName()) === mb_strtolower($parsed['category'] ?? '') || $category->getSlug() === ($parsed['category'] ?? '')) {
                $matches[] = $category;
            }
        }

        if (1 !== count($matches)) {
            throw ValidationException::fromFieldError('command', 'Choose an unambiguous existing category.');
        }
        $input = new AddFeedInput($parsed['value'], $matches[0]->getId()->toRfc4122());
        $this->validate($input);
        ($this->addFeed)(new AddFeedCommand($input->url, $input->categoryId, $ownerId));

        return '/feeds';
    }

    private function validate(object $input): void
    {
        $violations = $this->validator->validate($input);

        if (count($violations) > 0) {
            throw ValidationException::fromFieldError('command', 'Command contains invalid values.');
        }
    }
}
