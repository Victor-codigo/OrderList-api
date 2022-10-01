<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Mailer;

use Common\Adapter\Mailer\MailerSymfonyAdapter;
use Common\Domain\Mailer\MailerSentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Test\Unit\Common\Adapter\Mailer\Fixtures\TransportException;

class MailerSymfonyAdapterTest extends TestCase
{
    private MailerSymfonyAdapter $object;
    private MockObject|MailerInterface $mailer;

    public function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->getMockForAbstractClass(MailerInterface::class);
        $this->object = new MailerSymfonyAdapter($this->mailer);
    }

    /** @test */
    public function sendAnEmailOk(): void
    {
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(TemplatedEmail::class));

        $this->object->send();
    }

    /** @test */
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
