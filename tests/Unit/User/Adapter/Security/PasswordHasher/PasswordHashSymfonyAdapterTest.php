<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\PasswordHasher;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use User\Adapter\Security\PasswordHash\PasswordHashSymfonyAdapter;
use User\Adapter\Security\User\UserSymfonyAdapter;

class PasswordHashSymfonyAdapterTest extends TestCase
{
    private PasswordHashSymfonyAdapter $object;
    private MockObject|UserPasswordHasherInterface $passwordHasher;

    public function setUp(): void
    {
        parent::setUp();

        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->object = new PasswordHashSymfonyAdapter($this->passwordHasher);
    }

    /** @test */
    public function hashAPlainPassword(): void
    {
        $plainPassword = 'my password';
        $passowrd = '4d264267-66ff-4abb-8292-777b9b13d63b';

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(UserSymfonyAdapter::class), $plainPassword)
            ->willReturn($passowrd);

        $return = $this->object->passwordHash($plainPassword);

        $this->assertEquals(ValueObjectFactory::createPassword($passowrd), $return);
    }

    /** @test */
    public function checkIfAPasswordNeedsRehash(): void
    {
        $passowrd = ValueObjectFactory::createPassword('4d264267-66ff-4abb-8292-777b9b13d63b');

        $this->passwordHasher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->isInstanceOf(UserSymfonyAdapter::class))
            ->willReturn(true);

        $return = $this->object->passwordNeedsRehash($passowrd);

        $this->assertTrue($return);
    }

    /** @test */
    public function checkIfAPasswordIsValidNoNeedRehash(): void
    {
        $plainPassword = 'My password';

        $this->passwordHasher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->isInstanceOf(UserSymfonyAdapter::class))
            ->willReturn(false);

        $this->passwordHasher
            ->expects($this->never())
            ->method('hashPassword');

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->isInstanceOf(UserSymfonyAdapter::class), $plainPassword)
            ->willReturn(true);

        $return = $this->object->passwordIsValid($plainPassword);

        $this->assertTrue($return);
    }

    /** @test */
    public function checkIfAPasswordIsValidNeedRehash(): void
    {
        $plainPassword = 'My password';
        $passwordHashed = '5632c12e-1fa6-4394-a51d-dbba0e6cf00d';

        $this->passwordHasher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->isInstanceOf(UserSymfonyAdapter::class))
            ->willReturn(true);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(UserSymfonyAdapter::class), $plainPassword)
            ->willReturn($passwordHashed);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->isInstanceOf(UserSymfonyAdapter::class), $plainPassword)
            ->willReturn(true);

        $return = $this->object->passwordIsValid($plainPassword);

        $this->assertTrue($return);
    }
}
