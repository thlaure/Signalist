<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\ExternalAccount;
use App\Entity\Feed;
use App\Entity\User;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

use function is_array;

use const JSON_THROW_ON_ERROR;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;

final class RoadmapApiLifecycleTest extends DatabaseTestCase
{
    private KernelBrowser $client;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = new User()->setEmail('owner@example.com')->setPassword('hash')->setEmailVerifiedAt(new DateTimeImmutable());
        $this->entityManager->persist($this->owner);
        $this->entityManager->flush();
        $this->assertInstanceOf(KernelInterface::class, self::$kernel);
        $this->client = new KernelBrowser(self::$kernel);
        $this->client->disableReboot();
        $token = self::getContainer()->get(JWTTokenManagerInterface::class)->create($this->owner);
        $this->client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.$token);
    }

    public function testDraftEditingAndApprovedPublicationRemainScoped(): void
    {
        $date = new DateTimeImmutable()->format('Y-m-d');
        $recap = $this->api('POST', '/social-recaps', ['platform' => 'linkedin', 'start' => $date, 'end' => $date], 202);
        $id = $recap['id'];
        $this->assertIsString($id);
        $edited = $this->api('PUT', '/social-recaps/'.$id, ['text' => 'Reviewed source https://example.com/source'], 200);
        $this->assertSame('draft', $edited['status']);
        $this->api('GET', '/social-recaps/'.$id, null, 200);
        $this->api('GET', '/social-recaps', null, 200);
        $owner = $this->entityManager->find(User::class, $this->owner->getId());
        $this->assertInstanceOf(User::class, $owner);
        $account = new ExternalAccount($owner, 'x');
        $account->connected('test-ciphertext', new DateTimeImmutable('+1 hour'), 'remote-owner');
        $this->entityManager->persist($account);
        $this->entityManager->flush();
        $post = $this->api('POST', '/crossposts', ['accountId' => $account->getId()->toRfc4122(), 'text' => 'Approved source', 'confirmed' => true], 202);
        $this->assertSame('pending', $post['status']);
        $postId = $post['id'];
        $this->assertIsString($postId);
        $this->api('GET', '/crossposts/'.$postId, null, 200);
        $this->api('GET', '/crossposts', null, 200);
        $this->api('POST', '/crossposts', ['accountId' => $account->getId()->toRfc4122(), 'text' => 'Unapproved', 'confirmed' => false], 422);
        $this->api('DELETE', '/social-recaps/'.$id, null, 204);
        $this->api('GET', '/social-recaps/'.$id, null, 404);
        $this->api('DELETE', '/connections/'.$account->getId()->toRfc4122(), null, 204);
        $saved = $this->entityManager->find(ExternalAccount::class, $account->getId());
        $this->assertInstanceOf(ExternalAccount::class, $saved);
        $this->assertNull($saved->getEncryptedTokens());
    }

    public function testFulltextSearchSpotlightAndNewsletterLifecycle(): void
    {
        $category = new Category()->setOwner($this->owner)->setName('News')->setSlug('news');
        $feed = new Feed()->setOwner($this->owner)->setCategory($category)->setTitle('News')->setUrl('https://example.com/rss');
        $article = new Article()->setFeed($feed)->setGuid('guid')->setTitle('Useful facts')->setUrl('https://example.com/source');
        foreach ([$category, $feed, $article] as $entity) {
            $this->entityManager->persist($entity);
        } $this->entityManager->flush();
        $search = $this->api('POST', '/search', ['query' => 'Useful', 'mode' => 'fulltext'], 200);
        $this->assertSame('complete', $search['status']);
        $this->assertCount(1, $search['items']);
        $searchId = $search['id'];
        $this->assertIsString($searchId);
        $this->api('GET', '/search/'.$searchId, null, 200);
        $this->api('POST', '/spotlight', ['command' => 'create category Technology'], 200);
        $command = $this->api('POST', '/spotlight', ['command' => 'ask Useful facts'], 200);
        $this->assertSame('/search?mode=question&q=Useful%20facts', $command['path']);
        $newsletter = $this->api('POST', '/newsletters', ['title' => 'Brief', 'readingMinutes' => 5], 201);
        $id = $newsletter['id'];
        $this->assertIsString($id);
        $updated = $this->api('PUT', '/newsletters/'.$id, ['title' => 'Weekly brief', 'readingMinutes' => 10, 'intervalHours' => 168], 200);
        $this->assertSame(168, $updated['intervalHours']);
        $this->api('GET', '/newsletters/'.$id, null, 200);
        $this->api('GET', '/newsletters', null, 200);
        $this->api('POST', '/newsletters/'.$id.'/generate', [], 202);
        $denied = $this->api('POST', '/newsletters/'.$id.'/send', [], 403);
        $this->assertSame('https://signalist.app/problems/consent-required', $denied['type']);
        $this->api('DELETE', '/newsletters/'.$id, null, 204);
        $this->api('GET', '/newsletters/'.$id, null, 404);
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array<string|int, mixed>
     */
    private function api(string $method, string $path, ?array $body, int $status): array
    {
        $this->client->request($method, '/api/v1'.$path, server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], content: null === $body ? null : json_encode($body, JSON_THROW_ON_ERROR));
        $response = $this->client->getResponse();
        $this->assertSame($status, $response->getStatusCode(), $response->getContent() ?: '');
        $data = json_decode($response->getContent() ?: '{}', true);

        return is_array($data) ? $data : [];
    }
}
