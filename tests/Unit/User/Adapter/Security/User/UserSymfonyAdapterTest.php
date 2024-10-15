<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\User;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\User;

class UserSymfonyAdapterTest extends TestCase
{
    private UserSymfonyAdapter $object;
    private MockObject&User $user;
    private MockObject&UserPasswordHasherInterface $passwordHAsher;

    #[\Override]
    public function setup(): void
    {
        $this->loadUserSymfonyAdapter();
    }

    /**
     * @param string[] $userMethodsMock
     */
    private function loadUserSymfonyAdapter(array $userMethodsMock = []): void
    {
        $this->user = $this->createPartialMock(User::class, $userMethodsMock);
        $this->passwordHAsher = $this->createMock(UserPasswordHasherInterface::class);

        $this->object = new UserSymfonyAdapter($this->passwordHAsher, $this->user);
    }

    #[Test]
    public function returnsTheUser(): void
    {
        $return = $this->object->getUser();

        $this->assertEquals($return, $this->user,
            'getUser: User returned is not the expected');
    }

    #[Test]
    public function setsTheUser(): void
    {
        /** @var MockObject&User $userNew */
        $userNew = $this->createMock(User::class);
        $return = $this->object->setUser($userNew);

        $this->assertEquals($return, $this->object,
            'setUser: Doesn\'t return and instance of the class');

        $this->assertEquals($userNew, $this->object->getUser(),
            'setUser: The user set is not the expected');
    }

    #[Test]
    public function getRoles(): void
    {
        $roles = ValueObjectFactory::createRoles([new Rol(USER_ROLES::USER)]);
        $this->object->getUser()->setRoles($roles);
        $return = $this->object->getRoles();

        $this->assertEquals($return, [USER_ROLES::USER->value],
            'getRoles: The roles returned is not the expected');
    }

    #[Test]
    public function getTheIdentifier(): void
    {
        $email = ValueObjectFactory::createEmail('test@email.com');
        $this->object->getUser()->setEmail($email);
        $return = $this->object->getUserIdentifier();

        $this->assertEquals($return, $email->getValue(),
            'getUserIdentifier: The identifier returned is not the expected');
    }

    #[Test]
    public function getTheIdentifierWhenItsNullShouldReturnEmptyString(): void
    {
        $email = ValueObjectFactory::createEmail(null);
        $this->object->getUser()->setEmail($email);
        $return = $this->object->getUserIdentifier();

        $this->assertEquals($return, '');
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function itShouldHashTheUserPassword(): void
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

    #[Test]
    public function itShouldCheckIfThePasswordNeedRehash(): void
    {
        $this->passwordHAsher
            ->expects($this->once())
            ->method('needsRehash')
            ->with($this->object)
            ->willReturn(true);

        $return = $this->object->passwordNeedsRehash();

        $this->assertTrue($return);
    }

    #[Test]
    public function itShouldCheckIfAPasswordIsValidAndNeedRehash(): void
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

        $return = $this->object->passwordIsValid($plainPassword);

        $this->assertTrue($return);
    }

    #[Test]
    public function itShouldCheckIfAPasswordIsValidAndNotNeedRehash(): void
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

        $return = $this->object->passwordIsValid($plainPassword);

        $this->assertTrue($return);
    }

    #[Test]
    public function itShouldCheckIfAPasswordIsValidAndItIsNot(): void
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

        $return = $this->object->passwordIsValid($plainPassword);

        $this->assertFalse($return);
    }
}
