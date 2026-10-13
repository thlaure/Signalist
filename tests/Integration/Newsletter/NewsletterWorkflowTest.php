<?php

declare(strict_types=1);

namespace App\Tests\Integration\Newsletter;

use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Newsletter\DTO\NewsletterInput;
use App\Domain\Newsletter\Handler\NewsletterHandler;
use App\Domain\Newsletter\Message\DispatchNewsletterSchedulesMessage;
use App\Domain\Newsletter\Message\GenerateNewsletterMessage;
use App\Domain\Newsletter\MessageHandler\DispatchNewsletterSchedulesMessageHandler;
use App\Domain\Newsletter\MessageHandler\GenerateNewsletterMessageHandler;
use App\Domain\Newsletter\Service\NewsletterContentBuilder;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Newsletter;
use App\Entity\NewsletterRun;
use App\Entity\User;
use App\Entity\UserConsent;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class NewsletterWorkflowTest extends DatabaseTestCase
{
    public function testScheduleCreatesOneRunAndWithdrawalCancelsDelivery(): void
    {
        $owner = new User()->setEmail('owner@example.com')->setPassword('hash');
        $newsletter = new Newsletter($owner);
        $newsletter->configure('Scheduled brief', 5, 24);
        foreach ([$owner, $newsletter, new UserConsent($owner, 'newsletter', true)] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
        $this->entityManager->getConnection()->executeStatement("UPDATE newsletter SET next_run_at = '2020-01-01 00:00:00' WHERE id = :id", ['id' => $newsletter->getId()->toRfc4122()]);
        $this->entityManager->refresh($newsletter);
        $bus = self::getContainer()->get(MessageBusInterface::class);
        $consent = new ConsentPolicy($this->entityManager);
        $scheduler = new DispatchNewsletterSchedulesMessageHandler($this->entityManager, $bus, $consent);
        $scheduler(new DispatchNewsletterSchedulesMessage());
        $scheduler(new DispatchNewsletterSchedulesMessage());
        $this->assertSame(1, $this->entityManager->getRepository(NewsletterRun::class)->count([]));
        $run = $this->entityManager->getRepository(NewsletterRun::class)->findOneBy(['newsletter' => $newsletter]);
        $this->assertInstanceOf(NewsletterRun::class, $run);
        $this->entityManager->persist(new UserConsent($owner, 'newsletter', false));
        $this->entityManager->flush();
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');
        new GenerateNewsletterMessageHandler($this->entityManager, new NewsletterContentBuilder(new ArticleTextCleaner()), $consent, $mailer)(new GenerateNewsletterMessage($run->getId()->toRfc4122()));
        $this->assertSame('cancelled', $run->getStatus());
    }

    public function testDraftGenerationIsIdempotentAndDoesNotSendMail(): void
    {
        $owner = new User()->setEmail('owner@example.com')->setPassword('hash');
        $this->entityManager->persist($owner);
        $this->entityManager->flush();
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));
        $consent = new ConsentPolicy($this->entityManager);
        $handler = new NewsletterHandler($this->entityManager, $bus, $consent);
        $newsletter = $handler->save($owner, new NewsletterInput('Daily brief', 5, 24));
        $this->assertNotNull($newsletter->getNextRunAt());
        $run = $handler->generate($owner, $newsletter->getId()->toRfc4122());
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');
        $generator = new GenerateNewsletterMessageHandler($this->entityManager, new NewsletterContentBuilder(new ArticleTextCleaner()), $consent, $mailer);
        $generator(new GenerateNewsletterMessage($run->getId()->toRfc4122()));
        $generator(new GenerateNewsletterMessage($run->getId()->toRfc4122()));
        $this->assertSame('draft', $run->getStatus());
        $this->assertStringContainsString('Daily brief', $newsletter->getHtml() ?? '');
        $other = new User()->setEmail('other@example.com')->setPassword('hash');
        $this->entityManager->persist($other);
        $this->entityManager->flush();
        $this->expectException(NotFoundHttpException::class);
        $handler->get($other, $newsletter->getId()->toRfc4122());
    }
}
