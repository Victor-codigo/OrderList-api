<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Service\ShopRemoveAllGroupsShops;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopRemoveAllGroupsShops\Dto\ShopRemoveAllGroupsShopsDto;
use Shop\Domain\Service\ShopRemoveAllGroupsShops\ShopRemoveAllGroupsShopsService;

class ShopRemoveAllGroupsShopsServiceTest extends TestCase
{
    private const string SHOP_IMAGE_PATH = 'path/to/shop/images';
    private const string GROUP_ID_1 = 'group id 1';
    private const string GROUP_ID_2 = 'group id 2';

    private ShopRemoveAllGroupsShopsService $object;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|EntityImageRemoveService $entityImageRemoveService;
    private MockObject|PaginatorInterface $shopsPaginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->entityImageRemoveService = $this->createMock(EntityImageRemoveService::class);
        $this->shopsPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ShopRemoveAllGroupsShopsService(
            $this->shopRepository,
            $this->entityImageRemoveService,
            self::SHOP_IMAGE_PATH
        );
    }

    private function getGroupsShops(): array
    {
        return [
            Shop::fromPrimitives(
                'shop 1 id',
                'group 1',
                'shop 1 name',
                'shop 1 description',
                null
            ),
            Shop::fromPrimitives(
                'shop 2 id',
                'group 1',
                'shop 2 name',
                'shop 2 description',
                null
            ),
            Shop::fromPrimitives(
                'shop 3 id',
                'group 1',
                'shop 3 name',
                'shop 3 description',
                null
            ),
            Shop::fromPrimitives(
                'shop 4 id',
                'group 2',
                'shop 4 name',
                'shop 4 description',
                null
            ),
        ];
    }

    /**
     * @param Shop[] $shops
     */
    private function getShopsId(array $shops): array
    {
        return array_map(
            fn (Shop $shop) => $shop->getId(),
            $shops
        );
    }

    /** @test */
    public function itShouldRemoveAllShopsFromGroups(): void
    {
        $shops = $this->getGroupsShops();
        $shopsId = $this->getShopsId($shops);
        $shopImagePath = ValueObjectFactory::createPath(self::SHOP_IMAGE_PATH);
        $input = new ShopRemoveAllGroupsShopsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->shopRepository
            ->expects($this->once())
            ->method('findGroupsShopsOrFail')
            ->with($input->groupsId)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($shops));

        $this->entityImageRemoveService
            ->expects($this->exactly(count($shops)))
            ->method('__invoke')
            ->with(
                $this->callback(fn (Shop $shop) => $this->assertContainsEquals($shop, $shops) || true),
                $shopImagePath
            );

        $this->shopRepository
            ->expects($this->once())
            ->method('remove')
            ->with($shops);

        $return = $this->object->__invoke($input);

        $this->assertEquals($shopsId, $return);
    }

    /** @test */
    public function itShouldFailNoShopsFound(): void
    {
        $input = new ShopRemoveAllGroupsShopsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->shopRepository
            ->expects($this->once())
            ->method('findGroupsShopsOrFail')
            ->with($input->groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getAllPages');

        $this->entityImageRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->shopRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemoveImagenError(): void
    {
        $shops = $this->getGroupsShops();
        $shopImagePath = ValueObjectFactory::createPath(self::SHOP_IMAGE_PATH);
        $input = new ShopRemoveAllGroupsShopsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->shopRepository
            ->expects($this->once())
            ->method('findGroupsShopsOrFail')
            ->with($input->groupsId)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($shops));

        $this->entityImageRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(fn (Shop $shop) => $this->assertContainsEquals($shop, $shops) || true),
                $shopImagePath
            )
            ->willThrowException(new DomainInternalErrorException());

        $this->shopRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(DomainInternalErrorException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemoveEntitiesError(): void
    {
        $shops = $this->getGroupsShops();
        $shopImagePath = ValueObjectFactory::createPath(self::SHOP_IMAGE_PATH);
        $input = new ShopRemoveAllGroupsShopsDto([
            ValueObjectFactory::createIdentifier(self::GROUP_ID_1),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ]);

        $this->shopRepository
            ->expects($this->once())
            ->method('findGroupsShopsOrFail')
            ->with($input->groupsId)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getAllPages')
            ->with(100)
            ->willReturnCallback(fn () => yield new \ArrayIterator($shops));

        $this->entityImageRemoveService
            ->expects($this->exactly(count($shops)))
            ->method('__invoke')
            ->with(
                $this->callback(fn (Shop $shop) => $this->assertContainsEquals($shop, $shops) || true),
                $shopImagePath
            );

        $this->shopRepository
            ->expects($this->once())
            ->method('remove')
            ->with($shops)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
