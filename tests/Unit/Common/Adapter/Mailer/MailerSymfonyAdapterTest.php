<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Mailer;

use Common\Adapter\Mailer\MailerSymfonyAdapter;
use Common\Domain\Mailer\Exception\MailerSentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Test\Unit\Common\Adapter\Mailer\Fixtures\TransportException;

class MailerSymfonyAdapterTest extends TestCase
{
    private MailerSymfonyAdapter $object;
    private MockObject&MailerInterface $mailer;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->createMock(MailerInterface::class);
        $this->object = new MailerSymfonyAdapter($this->mailer);
    }

    #[Test]
    public function sendAnEmailOk(): void
    {
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(TemplatedEmail::class));

        $this->object->send();
    }

    #[Test]
    public function sendAnEmailThrow(): void
    {
        $this->expectException(MailerSentException::class);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(TemplatedEmail::class))
            ->willThrowException(new TransportException());

        $this->object->send();
    }
}
