<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\User;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class UserSymfonyAdapterTest extends TestCase
{
    private UserSymfonyAdapter $object;
    private MockObject|User $user;
    private MockObject|UserPasswordHasherInterface $passwordHAsher;

    public function setup(): void
    {
        $this->loadUserSymfonyAdapter();
    }

    private function loadUserSymfonyAdapter(array $userMethodMock = [])
    {
        $this->user = $this->createPartialMock(User::class, $userMethodMock);
        $this->passwordHAsher = $this->createMock(UserPasswordHasherInterface::class);

        $this->object = new UserSymfonyAdapter($this->passwordHAsher, $this->user);
    }

    /** @test */
    public function returnsTheUser(): void
    {
        $return = $this->object->getUser();

        $this->assertEquals($return, $this->user,
            'getUser: User returned is not the expected');
    }

    /** @test */
    public function setsTheUser(): void
    {
        $userNew = $this->createMock(User::class);
        $return = $this->object->setUser($userNew);

        $this->assertEquals($return, $this->object,
            'setUser: Doesn\'t return and instance of the class');

        $this->assertEquals($userNew, $this->object->getUser(),
            'setUser: The user set is not the expected');
    }

    /** @test */
    public function getRoles(): void
    {
        $roles = ValueObjectFactory::createRoles([new Rol(USER_ROLES::USER)]);
        $this->object->getUser()->setRoles($roles);
        $return = $this->object->getRoles();

        $this->assertEquals($return, [USER_ROLES::USER->value],
            'getRoles: The roles returned is not the expected');
    }

    /** @test */
    public function getTheIdentifier(): void
    {
        $email = ValueObjectFactory::createEmail('test@email.com');
        $this->object->getUser()->setEmail($email);
        $return = $this->object->getUserIdentifier();

        $this->assertEquals($return, $email->getValue(),
            'getUserIdentifier: The identifier returned is not the expected');
    }

    /** @test */
    public function getTheIdentifierWhenItsNullShouldReturnEmptyString(): void
    {
        $email = ValueObjectFactory::createEmail(null);
        $this->object->getUser()->setEmail($email);
        $return = $this->object->getUserIdentifier();

        $this->assertEquals($return, '');
    }

    /** @test */
    public function getThePassword(): void
    {
        $password = ValueObjectFactory::createPassword('pass');
        $this->loadUserSymfonyAdapter(['getPassword']);

        $this->user
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn($password);

        $this->object->getUser()->setPassword($password);
        $return = $this->object->getPassword();

        $this->assertEquals($return, $password->getValue(),
            'getPassword: The password returned is not the expected');
    }

    /** @test */
    public function getThePasswordWhenItsNullShouldReturnEmptyString(): void
    {
        $password = ValueObjectFactory::createPassword(null);
        $this->loadUserSymfonyAdapter(['getPassword']);

        $this->user
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn($password);

        $this->object->getUser()->setPassword($password);
        $return = $this->object->getPassword();

        $this->assertEquals($return, '');
    }

    /** @test */
    public function itShouldHashTheUserPassword()
    {
        $plainPassword = 'my password';
        $hashedPassword = $plainPassword.'-hashed';
        $this->loadUserSymfonyAdapter();

        $this->passwordHAsher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->object, $plainPassword)
            ->willReturn($hashedPassword);

        $this->object->passwordHash($plainPassword);

        $this->assertSame($hashedPassword, $this->object->getPassword());
    }

    /** @test */
    public function itShouldCheckIfThePasswordNeedRehash()
    {
        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(true);

        $resturn = $this->object->passwordNeedsRehash();

        $this->assertTrue($resturn);
    }

    /** @test */
    public function itShouldCheckIfAPasswordIsValidAndNeedRehash()
    {
        $plainPassword = 'my password';
        $hashedPassword = $plainPassword.'-hashed';

        $this->passwordHAsher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->object, $plainPassword)
            ->willReturn($hashedPassword);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(true);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->object, $plainPassword)
            ->willReturn(true);

        $resturn = $this->object->passwordIsValid($plainPassword);

        $this->assertTrue($resturn);
    }

    /** @test */
    public function itShouldCheckIfAPasswordIsValidAndNotNeedRehash()
    {
        $plainPassword = 'my password';

        $this->passwordHAsher
            ->expects($this->never())
            ->method('hashPassword');

        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(false);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->object, $plainPassword)
            ->willReturn(true);

        $resturn = $this->object->passwordIsValid($plainPassword);

        $this->assertTrue($resturn);
    }

    /** @test */
    public function itShouldCheckIfAPasswordIsValidAndItIsNot()
    {
        $plainPassword = 'my password';

        $this->passwordHAsher
            ->expects($this->never())
            ->method('hashPassword');

        $this->passwordHAsher
            ->expects($this->never())
            ->method('needsRehash');

        $this->passwordHAsher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->object, $plainPassword)
            ->willReturn(false);

        $resturn = $this->object->passwordIsValid($plainPassword);

        $this->assertFalse($resturn);
    }
}
