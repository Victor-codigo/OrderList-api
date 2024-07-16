<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\Jwt\JwtListener;

use Common\Domain\Validation\User\USER_ROLES;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use User\Adapter\Security\Jwt\Listener\JwtEncodedListener;
use User\Domain\Model\User;

class JwtEncodedListenerTest extends TestCase
{
    private const string TOKEN = 'token string';

    private JwtEncodedListener $object;
    private MockObject|JWTEncodedEvent $jwtEventEncoded;
    private MockObject|User $user;
    private MockObject|Security $security;
    private MockObject|RequestStack $requestStack;
    private MockObject|Request $request;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createMock(UserInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = new Request();
        $this->jwtEventEncoded = $this->getMockBuilder(JWTEncodedEvent::class)
            ->setConstructorArgs([self::TOKEN])
            ->onlyMethods(['getJWTString'])
            ->getMock();
        $this->object = new JwtEncodedListener($this->security, $this->requestStack);
    }

    /** @test */
    public function itShouldAddToHeadersTheTokenCreated(): void
    {
        $userRoles = [USER_ROLES::USER_FIRST_LOGIN->value];

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($userRoles);

        $this->jwtEventEncoded
            ->expects($this->once())
            ->method('getJWTString')
            ->willReturn(self::TOKEN);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->object->onJwtEncoded($this->jwtEventEncoded);

        $this->assertTrue($this->request->headers->has('Authorization'));
        $this->assertEquals('Bearer '.self::TOKEN, $this->request->headers->get('Authorization'));
    }

    /** @test */
    public function itShouldNotAddToHeadersTheTokenCreated(): void
    {
        $userRoles = [USER_ROLES::USER->value];

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($userRoles);

        $this->jwtEventEncoded
            ->expects($this->never())
            ->method('getJWTString');

        $this->requestStack
            ->expects($this->never())
            ->method('getCurrentRequest');

        $this->object->onJwtEncoded($this->jwtEventEncoded);
    }
}
