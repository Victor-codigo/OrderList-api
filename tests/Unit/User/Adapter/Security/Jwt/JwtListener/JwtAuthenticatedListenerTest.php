<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\Jwt\JwtListener;

use Common\Domain\Ports\Event\EventDispatcherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use User\Adapter\Security\Jwt\Listener\JwtAuthenticatedListener;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Event\UserLogin\UserLoginEvent;
use User\Domain\Model\User;

class JwtAuthenticatedListenerTest extends TestCase
{
    private JwtAuthenticatedListener $object;
    private MockObject&EventDispatcherInterface $eventDispatcher;
    private MockObject&Security $security;
    private MockObject&UserSymfonyAdapter $userAdapter;
    private MockObject&UserInterface $userAuthenticationEvent;
    private MockObject&User $user;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->userAdapter = $this->createMock(UserSymfonyAdapter::class);
        $this->userAuthenticationEvent = $this->createMock(UserInterface::class);
        $this->user = $this->createMock(User::class);
        $this->object = new JwtAuthenticatedListener($this->eventDispatcher, $this->security);
    }

    #[Test]
    public function itShouldNotDispatchUserLoginEventDispatcherIsNotAnUser(): void
    {
        $response = new Response();
        $eventAuthentication = new AuthenticationSuccessEvent([], $this->userAuthenticationEvent, $response);
        $eventUserLoginEvent = new UserLoginEvent($this->user);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userAdapter);

        $this->userAdapter
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (UserLoginEvent $event) use ($eventUserLoginEvent): bool {
                $this->assertEquals($eventUserLoginEvent->user, $event->user);
                $this->assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());

                return true;
            }));

        $this->object->onAuthenticationSuccess($eventAuthentication);
    }
}
