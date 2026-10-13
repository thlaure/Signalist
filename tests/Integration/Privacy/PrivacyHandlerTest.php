<?php

declare(strict_types=1);

namespace App\Tests\Integration\Privacy;

use App\Domain\Privacy\Handler\PrivacyHandler;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\Category;
use App\Entity\ExternalAccount;
use App\Entity\Feed;
use App\Entity\User;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

use const JSON_THROW_ON_ERROR;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class PrivacyHandlerTest extends DatabaseTestCase
{
    private function user(string $email): User
    {
        $user = new User()->setEmail($email)->setPassword('hash');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function handler(bool $validPassword = true): PrivacyHandler
    {
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('isPasswordValid')->willReturn($validPassword);

        return new PrivacyHandler($this->entityManager, new ConsentPolicy($this->entityManager), $hasher);
    }

    public function testConsentRequiresGrantAndHonorsWithdrawalAndRestriction(): void
    {
        $user = $this->user('owner@example.com');
        $handler = $this->handler();
        $policy = new ConsentPolicy($this->entityManager);
        $this->assertFalse($policy->allows($user, 'external_ai'));
        $handler->consent($user, 'external_ai', true);
        $this->assertTrue($policy->allows($user, 'external_ai'));
        $handler->consent($user, 'external_ai', false);
        $this->assertFalse($policy->allows($user, 'external_ai'));
        $handler->consent($user, 'external_ai', true);
        $handler->restrict($user, true);
        $this->assertFalse($policy->allows($user, 'external_ai'));
    }

    public function testExportAndDeletionAreScopedAndExcludeCredentials(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        $account = new ExternalAccount($owner, 'x');
        $account->connected('private-ciphertext', new DateTimeImmutable('+1 hour'), 'remote-owner');
        $this->entityManager->persist($account);
        $accountId = $account->getId();
        $ids = [];
        foreach ([$owner, $other] as $user) {
            $category = new Category()->setName('News')->setSlug('news')->setOwner($user);
            $feed = new Feed()->setTitle('News')->setUrl('https://example.com/rss')->setCategory($category)->setOwner($user);
            $article = new Article()->setTitle($user->getEmail())->setGuid('guid')->setUrl('https://example.com/a')->setFeed($feed);
            $bookmark = new Bookmark()->setArticle($article)->setNotes('private note');
            foreach ([$category, $feed, $article, $bookmark] as $entity) {
                $this->entityManager->persist($entity);
            }
            $ids[] = $article->getId();
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        $owner = $this->entityManager->find(User::class, $owner->getId());
        $this->assertInstanceOf(User::class, $owner);
        $handler = $this->handler();
        $json = json_encode($handler->export($owner), JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('other@example.com', $json);
        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString('private-ciphertext', $json);
        $this->assertStringContainsString('private note', $json);
        $handler->delete($owner, 'confirmed');
        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Article::class, $ids[0]));
        $this->assertNull($this->entityManager->find(ExternalAccount::class, $accountId));
        $this->assertInstanceOf(Article::class, $this->entityManager->find(Article::class, $ids[1]));
        $deleted = $this->entityManager->find(User::class, $owner->getId());
        $this->assertInstanceOf(User::class, $deleted);
        $this->assertTrue($deleted->isDeleted());
        $this->assertStringNotContainsString('owner@example.com', $deleted->getEmail());
    }

    public function testWrongPasswordDoesNotDeleteAccount(): void
    {
        $user = $this->user('owner@example.com');
        $this->expectException(AccessDeniedException::class);
        $this->handler(false)->delete($user, 'wrong');
    }
}
