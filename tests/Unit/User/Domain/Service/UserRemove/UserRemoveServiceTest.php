<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\UserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\UserRemove\BuiltInFunctionsReturn;
use User\Domain\Service\UserRemove\Dto\UserRemoveDto;
use User\Domain\Service\UserRemove\UserRemoveService;

require_once 'tests/BuiltinFunctions/UserRemoveService.php';

class UserRemoveServiceTest extends TestCase
{
    private const USER_IMAGE_PATH = 'image';
    private const USER_ID = 'user_id';
    private const USER_IMAGE_NAME = 'IMAGE NAME';

    private UserRemoveService $object;
    private MockObject|UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->object = new UserRemoveService($this->userRepository, self::USER_IMAGE_PATH);
    }

    private function setUserAsDeleted(User $user): void
    {
        $user->setEmail(ValueObjectFactory::createEmail(''));
        $user->setName(ValueObjectFactory::createName(''));
        $user->setPassword(ValueObjectFactory::createPassword(''));
        $user->setRoles(ValueObjectFactory::createRoles([new Rol(USER_ROLES::DELETED)]));
        $user->getProfile()->setImage(ValueObjectFactory::createPath(null));
    }

    private function createUserDefault(): User
    {
        $user = User::fromPrimitives(self::USER_ID, 'default@email.com', 'default password', 'default', [USER_ROLES::USER]);
        $user->getProfile()->setImage(ValueObjectFactory::createPath(self::USER_IMAGE_NAME));

        return $user;
    }

    /** @test */
    public function itShouldRemoveTheUserUserHasImage(): void
    {
        $userRemoveDto = new UserRemoveDto(ValueObjectFactory::createIdentifier(self::USER_ID));
        $user = $this->createUserDefault();
        $userDeleted = clone $user;
        $userDeleted->setProfile(clone $user->getProfile());
        $this->setUserAsDeleted($userDeleted);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($userRemoveDto->userId)
            ->willReturn($user);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($userDeleted);

        $this->object->__invoke($userRemoveDto);
    }

    /** @test */
    public function itShouldRemoveTheUserUserHasNoImage(): void
    {
        $userRemoveDto = new UserRemoveDto(ValueObjectFactory::createIdentifier(self::USER_ID));
        $user = $this->createUserDefault();
        $user->getProfile()->setImage(ValueObjectFactory::createPath(null));
        $userDeleted = clone $user;
        $userDeleted->setProfile(clone $user->getProfile());
        $this->setUserAsDeleted($userDeleted);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($userRemoveDto->userId)
            ->willReturn($user);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($userDeleted);

        $this->object->__invoke($userRemoveDto);
    }

    /** @test */
    public function itShouldFailUserIdDoesNotExist(): void
    {
        $userRemoveDto = new UserRemoveDto(ValueObjectFactory::createIdentifier(self::USER_ID));

        $this->expectException(DBNotFoundException::class);
        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($userRemoveDto->userId)
            ->willThrowException(DBNotFoundException::fromMessage());

        $this->object->__invoke($userRemoveDto);
    }

    /** @test */
    public function itShouldFailErrorDeletingUserImage(): void
    {
        $userRemoveDto = new UserRemoveDto(ValueObjectFactory::createIdentifier(self::USER_ID));
        $user = $this->createUserDefault();
        $user->getProfile()->setImage(ValueObjectFactory::createPath(self::USER_IMAGE_NAME));

        $this->expectException(DomainInternalErrorException::class);
        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($userRemoveDto->userId)
            ->willReturn($user);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;
        $this->object->__invoke($userRemoveDto);
    }
}
