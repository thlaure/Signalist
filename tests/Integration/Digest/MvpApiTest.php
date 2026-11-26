<?php

declare(strict_types=1);

namespace App\Tests\Integration\Digest;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\WeeklyDigest;
use App\Infrastructure\EventListener\MvpScopeListener;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use function is_array;

use const JSON_THROW_ON_ERROR;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class MvpApiTest extends DatabaseTestCase
{
    private KernelBrowser $client;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = new User()->setEmail('mvp@example.test')->setPassword('hash')->setEmailVerifiedAt(new DateTimeImmutable());
        $this->entityManager->persist($this->owner);
        $this->entityManager->flush();
        $this->assertInstanceOf(KernelInterface::class, self::$kernel);
        $this->client = new KernelBrowser(self::$kernel);
        $this->client->disableReboot();
        $this->client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.self::getContainer()->get(JWTTokenManagerInterface::class)->create($this->owner));
        self::getContainer()->get(EventDispatcherInterface::class)->addListener('kernel.request', new MvpScopeListener(true)->request(...), 240);
    }

    public function testRssSourceLifecycleAndQuotaAreEnforcedByApi(): void
    {
        $this->api('GET', '/user/profile', null, 200);
        $this->api('POST', '/user/consent', ['purpose' => 'newsletter', 'granted' => true], 204);
        $source = $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/rss'], 201);
        $this->assertIsString($source['id']);
        $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/rss'], 409);
        $this->api('POST', '/sources', ['url' => 'http://127.0.0.1/private'], 422);
        $this->api('POST', '/sources', ['url' => 'not-a-url'], 422);
        $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/rss2'], 201);
        $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/rss3'], 201);
        $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/rss4'], 402);
        $sources = $this->api('GET', '/sources', null, 200);
        $this->assertIsArray($sources['items']);
        $this->assertCount(3, $sources['items']);
        $this->assertSame(3, $sources['limit']);
        $this->api('DELETE', '/sources/'.$source['id'], null, 204);
        $this->api('DELETE', '/sources/'.$source['id'], null, 404);
    }

    public function testHistoryIsImmutableAndCannotBeReadByAnotherUser(): void
    {
        $other = new User()->setEmail('other@example.test')->setPassword('hash');
        $digest = new WeeklyDigest($this->owner, '2026-10-12');
        $digest->complete([['title' => 'Source title', 'url' => 'https://example.com/article', 'source' => 'RSS', 'summary' => 'Summary.']]);
        $foreign = new WeeklyDigest($other, '2026-10-12');
        foreach ([$other, $digest, $foreign] as $entity) {
            $this->entityManager->persist($entity);
        } $this->entityManager->flush();
        $list = $this->api('GET', '/digests', null, 200);
        $this->assertIsArray($list['items']);
        $this->assertCount(1, $list['items']);
        $this->api('GET', '/digests/'.$digest->getId()->toRfc4122(), null, 200);
        $this->api('GET', '/digests/'.$foreign->getId()->toRfc4122(), null, 404);
        $this->api('POST', '/digests', [], 405);
        $this->api('GET', '/digests/invalid', null, 404);
        $export = $this->api('GET', '/user/data-export', null, 200);
        $this->assertIsArray($export['digests']);
        $this->assertCount(1, $export['digests']);
    }

    public function testLegacyEndpointsAndOrganizationBillingAreUnavailable(): void
    {
        foreach (['/workspaces', '/organizations', '/connections', '/social-recaps', '/newsletters', '/feeds', '/articles', '/search', '/spotlight'] as $path) {
            $this->api('GET', $path, null, 404);
        }
        $this->api('GET', '/billing?organizationId=anything', null, 404);
        $this->api('POST', '/billing/links', ['kind' => 'checkout', 'organizationId' => 'anything'], 404);
        $this->api('POST', '/billing/checkout', [], 503);
        $this->api('POST', '/billing/portal', [], 503);
        $billing = $this->api('GET', '/billing', null, 200);
        $this->assertSame(['free' => 3, 'paid' => 20], $billing['offers']);
    }

    public function testSelectionCannotIncludeAnotherUsersSource(): void
    {
        $source = $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/rss'], 201);
        $this->api('PUT', '/sources/selection', ['ids' => ['00000000-0000-7000-8000-000000000001']], 404);
        $this->api('PUT', '/sources/selection', ['ids' => [$source['id']]], 204);
        $this->api('PUT', '/sources/selection', ['ids' => [$source['id'], $source['id']]], 422);
    }

    public function testPaidQuotaAndCancellationPreserveSourcesAndHistory(): void
    {
        $subscription = new Subscription($this->owner);
        $subscription->apply('active', 'team', new DateTimeImmutable('+1 month'));
        $this->entityManager->persist($subscription);
        $this->entityManager->flush();
        for ($i = 0; $i < 20; ++$i) {
            $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/rss'.$i], 201);
        }
        $this->api('POST', '/sources', ['url' => 'https://1.1.1.1/overflow'], 402);
        // A rejected transaction resets Doctrine for the next request. Resolve its current manager.
        $manager = self::getContainer()->get(ManagerRegistry::class)->resetManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $manager);
        $this->entityManager = $manager;
        $subscription = $this->entityManager->find(Subscription::class, $subscription->getId());
        $this->assertInstanceOf(Subscription::class, $subscription);
        $subscription->apply('inactive', 'free', null);
        $this->entityManager->flush();
        $sources = $this->api('GET', '/sources', null, 200);
        $this->assertSame(3, $sources['limit']);
        $this->assertTrue($sources['selectionRequired']);
        $this->assertIsArray($sources['items']);
        $this->assertCount(20, $sources['items']);
        $this->api('PUT', '/sources/selection', ['ids' => []], 204);
        $sources = $this->api('GET', '/sources', null, 200);
        $this->assertFalse($sources['selectionRequired']);
    }

    /** @param array<string, mixed>|null $body
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
