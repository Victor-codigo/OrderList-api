<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Security;

use Override;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserSharedSymfonyAdapterTest extends TestCase
{
    private UserSharedSymfonyAdapter $object;
    private MockObject|UserShared $userShared;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userShared = $this->createMock(UserShared::class);
        $this->object = new UserSharedSymfonyAdapter($this->userShared);
    }

    /** @test */
    public function itShouldGetTheRoles(): void
    {
        $roles = Roles::create([USER_ROLES::USER]);

        $this->userShared
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles);

        $return = $this->object->getRoles();

        $this->assertEquals([USER_ROLES::USER->value], $return);
    }

    /** @test */
    public function itShouldGetTheRolesNoRoles(): void
    {
        $roles = new Roles(null);

        $this->userShared
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles);

        $return = $this->object->getRoles();

        $this->assertEmpty($return);
    }
}
