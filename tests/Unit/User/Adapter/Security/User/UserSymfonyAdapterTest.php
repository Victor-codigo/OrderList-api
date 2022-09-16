<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\User;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

/** @covers UserSymfonyAdapter */
class UserSymfonyAdapterTest extends TestCase
{
    private UserSymfonyAdapter $object;
    private MockObject|User $user;
    private MockObject|UserPasswordHasherInterface $passwordHAsher;

    public function setup(): void
    {
        $this->user = $this->createPartialMock(User::class, []);
        $this->passwordHAsher = $this->createMock(UserPasswordHasherInterface::class);

        $this->object = new UserSymfonyAdapter($this->user, $this->passwordHAsher);
    }

    /** @test */
    public function getUserReturnsTheUser(): void
    {
        $return = $this->object->getUser();

        $this->assertEquals($return, $this->user,
            'getUser: User returned is not the expected');
    }

    /** @test */
    public function setUserSetTheUser(): void
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
        $roles = ValueObjectFactory::createRoles([USER_ROLES::USER]);
        $this->object->getUser()->setRoles($roles);
        $return = $this->object->getRoles();

        $this->assertEquals($return, $roles->getValue(),
            'getRoles: The roles returned is not the expected');
    }

    /** @test */
    public function eraseCredentialsRemoveData(): void
    {
        $this->object->eraseCredentials();

        $this->assertNull($this->object->getUser()->getPassword()->getValue(),
            'eraseCredentials: Doesn\'t remove the data');
    }

    /** @test */
    public function getUserIdentifierGetTheIdentifier(): void
    {
        $email = ValueObjectFactory::createEmail('test@email.com');
        $this->object->getUser()->setEmail($email);
        $return = $this->object->getUserIdentifier();

        $this->assertEquals($return, $email->getValue(),
            'getUserIdentifier: The identifier returned is not the expected');
    }

    /** @test */
    public function getPasswordGetThePassword(): void
    {
        $password = ValueObjectFactory::createPassword('pass');
        $this->object->getUser()->setPassword($password);
        $return = $this->object->getPassword();

        $this->assertEquals($return, $password->getValue(),
            'getPassword: The password returned is not the expected');
    }

    /** @test */
    public function passwordHashThePasswordIsHashed(): void
    {
        $password = ValueObjectFactory::createPassword('pass');
        $passwordHashed = 'hashed password';

        $this->passwordHAsher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->object, $password->getValue())
            ->willReturn($passwordHashed);

        $this->object->getUser()->setPassword($password);
        $return = $this->object->passwordHash();

        $this->assertEquals($return, $this->object,
            'passwordHash: Doesn\'t return and instance of the class');

        $this->assertEquals($this->object->getUser()->getPassword()->getValue(), $passwordHashed,
            'passwordIsValid: Hashed password is not correct');
    }

    /** @test */
    public function passwordIsValidPasswordNeedRehashAndValidPassword(): void
    {
        $password = ValueObjectFactory::createPassword('pass');
        $passwordHashed = 'hashed password';

        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(true);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->object, $password->getValue())
            ->willReturn(true);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->object, $password->getValue())
            ->willReturn($passwordHashed);

        $this->object->getUser()->setPassword($password);
        $return = $this->object->passwordIsValid($password->getValue());

        $this->assertTrue($return,
            'passwordIsValid: Password is not correct');

        $this->assertEquals($this->object->getUser()->getPassword()->getValue(), $passwordHashed,
            'passwordIsValid: Hashed password is not correct');
    }

    /** @test */
    public function passwordIsValidPasswordNeedRehashAndInvalidPassword(): void
    {
        $password = ValueObjectFactory::createPassword('pass');
        $passwordHashed = 'hashed password';

        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(true);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->object, $password->getValue())
            ->willReturn(false);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->object, $password->getValue())
            ->willReturn($passwordHashed);

        $this->object->getUser()->setPassword($password);
        $return = $this->object->passwordIsValid($password->getValue());

        $this->assertFalse($return,
            'passwordIsValid: Password is not correct');

        $this->assertEquals($this->object->getUser()->getPassword()->getValue(), $passwordHashed,
            'passwordIsValid: Hashed password is not correct');
    }

    /** @test */
    public function passwordIsValidPasswordIsValid(): void
    {
        $password = ValueObjectFactory::createPassword('pass');

        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(false);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->object, $password->getValue())
            ->willReturn(true);

        $this->object->getUser()->setPassword($password);
        $return = $this->object->passwordIsValid($password->getValue());

        $this->assertTrue($return,
            'passwordIsValid: Password is not correct');
    }

    /** @test */
    public function passwordIsValidPasswordNotValid(): void
    {
        $password = ValueObjectFactory::createPassword('pass');

        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(false);

        $this->passwordHAsher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->object, $password->getValue())
            ->willReturn(false);

        $this->object->getUser()->setPassword($password);
        $return = $this->object->passwordIsValid($password->getValue());

        $this->assertFalse($return,
            'passwordIsValid: Password is not incorrect');
    }
}
