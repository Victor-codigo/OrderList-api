<?php

declare(strict_types=1);

namespace Test\Unit\Share\Domain\Service\ShareListOrdersCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\User\USER_ROLES;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareListOrdersCreate\Dto\ShareListOrderCreateDto;
use Share\Domain\Service\ShareListOrdersCreate\ShareListOrdersCreateService;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;

class ShareListOrdersCreateServiceTest extends TestCase
{
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private const int SHARED_EXPIRATION_TIME = 30;

    private ShareListOrdersCreateService $object;
    private MockObject|ShareRepositoryInterface $shareRepository;
    private MockObject|ListOrdersRepositoryInterface $listOrdersRepository;
    private MockObject|PaginatorInterface $listOrdersRepositoryPaginator;
    private MockObject|UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shareRepository = $this->createMock(ShareRepositoryInterface::class);
        $this->listOrdersRepository = $this->createMock(ListOrdersRepositoryInterface::class);
        $this->listOrdersRepositoryPaginator = $this->createMock(PaginatorInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->object = new ShareListOrdersCreateService(
            $this->shareRepository,
            $this->listOrdersRepository,
            $this->userRepository,
            self::SHARED_EXPIRATION_TIME
        );
    }

    private function userCreate(): User
    {
        return User::fromPrimitives(
            self::USER_ID,
            'user@email.com',
            'user password',
            'user name',
            [USER_ROLES::USER]
        );
    }

    private function listOrdersCreate(): ListOrders
    {
        return ListOrders::fromPrimitives(
            self::LIST_ORDERS_ID,
            self::GROUP_ID,
            self::USER_ID,
            'list orders name',
            'list orders description',
            null
        );
    }

    #[Test]
    public function itShouldCreateANewShared(): void
    {
        $input = new ShareListOrderCreateDto(
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );
        $user = $this->userCreate();
        $listOrders = $this->listOrdersCreate();

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($input->userId)
            ->willReturn($user);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId])
            ->willReturn($this->listOrdersRepositoryPaginator);

        $this->listOrdersRepositoryPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->listOrdersRepositoryPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrders]));

        $this->shareRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Share $shareNew) use ($listOrders, $user) {
                $this->assertInstanceOf(Identifier::class, $shareNew->getId());
                $this->assertEquals($listOrders->getId(), $shareNew->getListOrdersId());
                $this->assertEquals($user->getId(), $shareNew->getUserId());
                $this->assertEqualsWithDelta(time() + self::SHARED_EXPIRATION_TIME, $shareNew->getExpire()->getTimestamp(), 1);

                return true;
            }));

        $return = $this->object->__invoke($input);

        $this->assertInstanceOf(Identifier::class, $return->getId());
        $this->assertEquals($listOrders->getId(), $return->getListOrdersId());
        $this->assertEquals($user->getId(), $return->getUserId());
        $this->assertEqualsWithDelta(time() + self::SHARED_EXPIRATION_TIME, $return->getExpire()->getTimestamp(), 1);
    }

    #[Test]
    public function itShouldFailCreateANewSharedUserIdNotFound(): void
    {
        $input = new ShareListOrderCreateDto(
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($input->userId)
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersRepository
            ->expects($this->never())
            ->method('findListOrderByIdOrFail');

        $this->listOrdersRepositoryPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->listOrdersRepositoryPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->shareRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailCreateANewSharedListOrdersIdNotFound(): void
    {
        $input = new ShareListOrderCreateDto(
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );

        $user = $this->userCreate();

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($input->userId)
            ->willReturn($user);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId])
            ->willThrowException(new DBNotFoundException());

        $this->listOrdersRepositoryPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->listOrdersRepositoryPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->shareRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailCreatingANewSharedSaveError(): void
    {
        $input = new ShareListOrderCreateDto(
            ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID),
            ValueObjectFactory::createIdentifier(self::USER_ID)
        );
        $user = $this->userCreate();
        $listOrders = $this->listOrdersCreate();

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($input->userId)
            ->willReturn($user);

        $this->listOrdersRepository
            ->expects($this->once())
            ->method('findListOrderByIdOrFail')
            ->with([$input->listOrdersId])
            ->willReturn($this->listOrdersRepositoryPaginator);

        $this->listOrdersRepositoryPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 1);

        $this->listOrdersRepositoryPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$listOrders]));

        $this->shareRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Share $shareNew) use ($listOrders, $user) {
                $this->assertInstanceOf(Identifier::class, $shareNew->getId());
                $this->assertEquals($listOrders->getId(), $shareNew->getListOrdersId());
                $this->assertEquals($user->getId(), $shareNew->getUserId());
                $this->assertEqualsWithDelta(time() + self::SHARED_EXPIRATION_TIME, $shareNew->getExpire()->getTimestamp(), 1);

                return true;
            }))
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
