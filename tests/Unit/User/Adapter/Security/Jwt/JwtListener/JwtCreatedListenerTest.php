<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\Jwt\JwtListener;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Adapter\Security\Jwt\Listener\JwtCreatedListener;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\User;

class JwtCreatedListenerTest extends TestCase
{
    private JwtCreatedListener $object;
    private MockObject&JWTCreatedEvent $jwtEventCreated;
    private MockObject&UserSymfonyAdapter $userAdapter;
    private MockObject&User $user;

    private const array EVENT_PARAMS = [
        'param1' => 1,
        'param2' => 2,
    ];

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userAdapter = $this->createMock(UserSymfonyAdapter::class);
        $this->user = $this->createMock(User::class);
        $this->jwtEventCreated = $this->getMockBuilder(JWTCreatedEvent::class)
            ->setConstructorArgs([self::EVENT_PARAMS, $this->userAdapter])
            ->onlyMethods(['setData'])
            ->getMock();
        $this->object = new JwtCreatedListener();
    }

    #[Test]
    public function itShouldSetUserIdInUsername(): void
    {
        $userId = ValueObjectFactory::createIdentifier('this is an id');
        $expected = array_merge(self::EVENT_PARAMS, ['username' => $userId->getValue()]);

        $this->userAdapter
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->jwtEventCreated
            ->expects($this->once())
            ->method('setData')
            ->with($expected);

        $this->object->onJWTCreated($this->jwtEventCreated);
    }
}
