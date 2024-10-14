<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Controller;

use Common\Adapter\Event\Controller\ControllerEventSubscriber;
use Common\Adapter\Http\TryoutPermissions\Exception\TryoutUserRoutePermissionsException;
use Common\Adapter\Http\TryoutPermissions\TryoutUserRoutePermissionsValidation;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ControllerEventSubscriberTest extends TestCase
{
    private const string USER_SESSION_ID = '372c70ab-2aca-4d47-99f1-e8ea45baaef3';
    private const string ROUTE_CURRENT = 'current_route';

    private ControllerEventSubscriber $object;
    private MockObject&TryoutUserRoutePermissionsValidation $tryoutUserPermissionsValidation;
    private MockObject&Security $security;
    // @phpstan-ignore property.unresolvableNativeType
    private MockObject&ControllerEvent $controllerEvent;
    private MockObject&UserSharedSymfonyAdapter $userSymfonyAdapter;
    private MockObject&UserShared $user;
    private Request $request;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->tryoutUserPermissionsValidation = $this->createMock(TryoutUserRoutePermissionsValidation::class);
        $this->security = $this->createMock(Security::class);
        // @phpstan-ignore method.unresolvableReturnType
        $this->controllerEvent = $this->createMock(ControllerEvent::class);
        $this->userSymfonyAdapter = $this->createMock(UserSharedSymfonyAdapter::class);
        $this->user = $this->createMock(UserShared::class);
        $this->object = new ControllerEventSubscriber($this->tryoutUserPermissionsValidation, $this->security);
        $this->request = new Request(
            [],
            [],
            [
                '_route' => self::ROUTE_CURRENT,
            ],
        );
    }

    #[Test]
    public function itShouldValidateRouteForTryoutUser(): void
    {
        $userSessionId = ValueObjectFactory::createIdentifier(self::USER_SESSION_ID);

        $this->controllerEvent
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userSymfonyAdapter);

        $this->userSymfonyAdapter
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userSessionId);

        $this->tryoutUserPermissionsValidation
            ->expects($this->once())
            ->method('__invoke')
            ->with($userSessionId, self::ROUTE_CURRENT);

        $this->object->__invoke($this->controllerEvent);
    }

    #[Test]
    public function itShouldValidateNoSessionUser(): void
    {
        $this->controllerEvent
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->userSymfonyAdapter
            ->expects($this->never())
            ->method('getUser');

        $this->user
            ->expects($this->never())
            ->method('getId');

        $this->tryoutUserPermissionsValidation
            ->expects($this->never())
            ->method('__invoke');

        $this->object->__invoke($this->controllerEvent);
    }

    #[Test]
    public function itShouldFailValidateRouteForTryoutUser(): void
    {
        $userSessionId = ValueObjectFactory::createIdentifier(self::USER_SESSION_ID);

        $this->controllerEvent
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userSymfonyAdapter);

        $this->userSymfonyAdapter
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userSessionId);

        $this->tryoutUserPermissionsValidation
            ->expects($this->once())
            ->method('__invoke')
            ->with($userSessionId, self::ROUTE_CURRENT)
            ->willThrowException(TryoutUserRoutePermissionsException::fromMessage(''));

        $this->expectException(TryoutUserRoutePermissionsException::class);
        $this->object->__invoke($this->controllerEvent);
    }
}
