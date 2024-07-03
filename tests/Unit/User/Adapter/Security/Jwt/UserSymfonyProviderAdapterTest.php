<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Security\Jwt;

use Override;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\User\USER_ROLES;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Test\Unit\User\Adapter\Security\Jwt\Fixtures\UserAdapter;
use User\Adapter\Security\Jwt\UserSymfonyProviderAdapter;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Port\User\UserInterface;

class UserSymfonyProviderAdapterTest extends TestCase
{
    private UserSymfonyProviderAdapter $object;
    private MockObject|UserRepositoryInterface $userRepository;
    private MockObject|UserPasswordHasherInterface $passwordHasher;
    private MockObject|UserAdapter $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(UserSymfonyAdapter::class);

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->object = new UserSymfonyProviderAdapter($this->userRepository, $this->passwordHasher);
    }

    /** @test */
    public function itShouldLoadUserById(): void
    {
        $id = 'this is an id';
        $expectedUser = User::fromPrimitives($id, 'user@email.com', 'password', 'name', [USER_ROLES::USER]);

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($id)
            ->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with(ValueObjectFactory::createIdentifier($id))
            ->willReturn($expectedUser);

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByEmailOrFail');

        $return = $this->object->loadUserByIdentifier($id);

        if (!$return instanceof UserInterface) {
            return;
        }

        $this->assertEquals($expectedUser, $return->getUser());
    }

    /** @test */
    public function itShouldFailUserIdDoesNotExists(): void
    {
        $id = 'this is an id';

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($id)
            ->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with(ValueObjectFactory::createIdentifier($id))
            ->willThrowException(DBNotFoundException::fromMessage(''));

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByEmailOrFail');

        $this->expectException(UserNotFoundException::class);
        $this->object->loadUserByIdentifier($id);
    }

    /** @test */
    public function itShouldLoadUserByEmail(): void
    {
        $email = 'user@email.com';
        $expectedUser = User::fromPrimitives('', $email, 'password', 'name', [USER_ROLES::USER]);

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($email)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByEmailOrFail')
            ->with(ValueObjectFactory::createEmail($email))
            ->willReturn($expectedUser);

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByIdOrFail');

        $return = $this->object->loadUserByIdentifier($email);

        if (!$return instanceof UserInterface) {
            return;
        }

        $this->assertEquals($expectedUser, $return->getUser());
    }

    /** @test */
    public function itShouldFailUserEmailDoesNotExists(): void
    {
        $email = 'user@email.com';

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($email)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByEmailOrFail')
            ->with(ValueObjectFactory::createEmail($email))
            ->willThrowException(DBNotFoundException::fromMessage(''));

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByIdOrFail');

        $this->expectException(UserNotFoundException::class);
        $this->object->loadUserByIdentifier($email);
    }

    /** @test */
    public function itShouldFailUserIsNotActive(): void
    {
        $email = 'user@email.com';
        $expectedUser = User::fromPrimitives('', $email, 'password', 'name', [USER_ROLES::NOT_ACTIVE]);

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($email)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByEmailOrFail')
            ->with(ValueObjectFactory::createEmail($email))
            ->willReturn($expectedUser);

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByIdOrFail');

        $this->expectException(UserNotFoundException::class);

        $this->object->loadUserByIdentifier($email);
    }

    /** @test */
    public function itShouldFailUserIsDeleted(): void
    {
        $email = 'user@email.com';
        $expectedUser = User::fromPrimitives('', $email, 'password', 'name', [USER_ROLES::DELETED]);

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($email)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByEmailOrFail')
            ->with(ValueObjectFactory::createEmail($email))
            ->willReturn($expectedUser);

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByIdOrFail');

        $this->expectException(UserNotFoundException::class);

        $this->object->loadUserByIdentifier($email);
    }

    /** @test */
    public function itShouldUpdateTheUserPassword(): void
    {
        $passwordNew = 'new password';
        $expectedUser = User::fromPrimitives('', '', '', '', []);

        $this->user
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($expectedUser);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($expectedUser);

        $this->object->upgradePassword($this->user, $passwordNew);

        $this->assertSame($passwordNew, $expectedUser->getPassword()->getValue());
    }

    /** @test */
    public function itShouldRefreshTheUser(): void
    {
        $email = 'user@email.com';
        $expectedUser = User::fromPrimitives('', $email, 'password', 'name', [USER_ROLES::USER]);

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($email)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByEmailOrFail')
            ->with(ValueObjectFactory::createEmail($email))
            ->willReturn($expectedUser);

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByIdOrFail');

        $this->user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($email);

        $return = $this->object->refreshUser($this->user);

        if (!$return instanceof UserInterface) {
            return;
        }

        $this->assertEquals($expectedUser, $return->getUser());
    }

    /** @test */
    public function itShouldFailUserNotSupported(): void
    {
        $this->user
            ->expects($this->never())
            ->method('getUserIdentifier');

        $this->expectException(UnsupportedUserException::class);
        $this->object->refreshUser(new UserAdapter());
    }

    /** @test */
    public function itShouldFailUserEmailNotFound(): void
    {
        $email = 'user@email.com';

        $this->userRepository
            ->expects($this->once())
            ->method('isValidUuid')
            ->with($email)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByEmailOrFail')
            ->with(ValueObjectFactory::createEmail($email))
            ->willThrowException(DBNotFoundException::fromMessage(''));

        $this->userRepository
            ->expects($this->never())
            ->method('findUserByIdOrFail');

        $this->user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($email);

        $this->expectException(UserNotFoundException::class);
        $this->object->refreshUser($this->user);
    }
}
