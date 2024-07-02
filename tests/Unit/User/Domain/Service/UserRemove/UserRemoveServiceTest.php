<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\UserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\UserRemove\Dto\UserRemoveDto;
use User\Domain\Service\UserRemove\UserRemoveService;

require_once 'tests/BuiltinFunctions/UserRemoveService.php';

class UserRemoveServiceTest extends TestCase
{
    private const string USER_IMAGE_PATH = 'image';
    private const string USER_ID = 'user_id';
    private const string USER_IMAGE_NAME = 'IMAGE NAME';

    private UserRemoveService $object;
    private MockObject|UserRepositoryInterface $userRepository;
    private MockObject|EntityImageRemoveService $entityImageRemoveService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->entityImageRemoveService = $this->createMock(EntityImageRemoveService::class);
        $this->object = new UserRemoveService(
            $this->userRepository,
            $this->entityImageRemoveService,
            self::USER_IMAGE_PATH
        );
    }

    private function getUser(): User
    {
        $user = User::fromPrimitives(
            self::USER_ID,
            'default@email.com',
            'default password',
            'default',
            [USER_ROLES::USER]
        );

        $user->getProfile()->setImage(ValueObjectFactory::createPath(self::USER_IMAGE_NAME));

        return $user;
    }

    /** @test */
    public function itShouldRemoveTheUserUser(): void
    {
        $user = $this->getUser();
        $userImagePath = ValueObjectFactory::createPath(self::USER_IMAGE_PATH);
        $input = new UserRemoveDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($input->userId)
            ->willReturn($user);

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($user, $userImagePath);

        $this->userRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$user]);

        $return = $this->object->__invoke($input);

        $this->assertEquals(self::USER_ID, $return);
    }

    /** @test */
    public function itShouldFailUserIdDoesNotExist(): void
    {
        $input = new UserRemoveDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($input->userId)
            ->willThrowException(DBNotFoundException::fromMessage());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingTheUser(): void
    {
        $user = $this->getUser();
        $userImagePath = ValueObjectFactory::createPath(self::USER_IMAGE_PATH);
        $input = new UserRemoveDto(
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($input->userId)
            ->willReturn($user);

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($user, $userImagePath);

        $this->userRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$user])
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
