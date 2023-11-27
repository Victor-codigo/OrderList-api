<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Service\ShopGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopGetData\Dto\ShopGetDataDto;
use Shop\Domain\Service\ShopGetData\ShopGetDataService;

class ShopGetDataServiceTest extends TestCase
{
    private ShopGetDataService $object;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ShopGetDataService($this->shopRepository);
    }

    private function getShops(): array
    {
        return [
            Shop::fromPrimitives('shop 1 id', 'group id', 'shop 1 name', 'shop 1 description', null),
            Shop::fromPrimitives('shop 2 id', 'group id', 'shop 2 name', 'shop 2 description', null),
            Shop::fromPrimitives('shop 3 id', 'group id', 'shop 3 name', 'shop 3 description', null),
        ];
    }

    private function assertShopDataIsOk(Shop $shopsDataExpected, array $shopDataActual): void
    {
        $this->assertArrayHasKey('id', $shopDataActual);
        $this->assertArrayHasKey('group_id', $shopDataActual);
        $this->assertArrayHasKey('name', $shopDataActual);
        $this->assertArrayHasKey('description', $shopDataActual);
        $this->assertArrayHasKey('image', $shopDataActual);
        $this->assertArrayHasKey('created_on', $shopDataActual);

        $this->assertEquals($shopsDataExpected->getId()->getValue(), $shopDataActual['id']);
        $this->assertEquals($shopsDataExpected->getGroupId()->getValue(), $shopDataActual['group_id']);
        $this->assertEquals($shopsDataExpected->getName()->getValue(), $shopDataActual['name']);
        $this->assertEquals($shopsDataExpected->getDescription()->getValue(), $shopDataActual['description']);
        $this->assertEquals($shopsDataExpected->getImage()->getValue(), $shopDataActual['image']);
        $this->assertIsString($shopDataActual['created_on']);
    }

    /** @test */
    public function itShouldGetShopsDataOrderByNameAsc(): void
    {
        $shops = $this->getShops();
        $shopNameStartsWith = null;
        $shopsMaxNumber = 100;
        $orderAsc = true;
        $shopName = ValueObjectFactory::createNameWithSpaces('Shop name');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $productsId = [
            ValueObjectFactory::createIdentifier('product 1 id'),
        ];
        $input = new ShopGetDataDto($groupId, $shopsId, $productsId, $shopNameStartsWith, $shopName, $shopsMaxNumber, $orderAsc);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($shopsId, $groupId, $productsId, $shopName, $shopNameStartsWith, $orderAsc)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, $shopsMaxNumber);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject($shops));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($shops), $return);

        foreach ($shops as $key => $shopExpected) {
            $this->assertShopDataIsOk($shopExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetShopsDataOrderByNameDesc(): void
    {
        $shops = $this->getShops();
        $shopNameStartsWith = null;
        $shopsMaxNumber = 100;
        $orderAsc = false;
        $shopName = ValueObjectFactory::createNameWithSpaces('Shop name');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $productsId = [
            ValueObjectFactory::createIdentifier('product 1 id'),
        ];
        $input = new ShopGetDataDto($groupId, $shopsId, $productsId, $shopNameStartsWith, $shopName, $shopsMaxNumber, $orderAsc);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($shopsId, $groupId, $productsId, $shopName, $shopNameStartsWith, $orderAsc)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, $shopsMaxNumber);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject(array_reverse($shops)));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($shops), $return);

        foreach (array_reverse($shops) as $key => $shopExpected) {
            $this->assertShopDataIsOk($shopExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetShopsDataAllInputsAreNull(): void
    {
        $shopNameStartsWith = null;
        $shopName = ValueObjectFactory::createNameWithSpaces(null);
        $groupId = ValueObjectFactory::createIdentifier(null);
        $shopsId = [];
        $shopsId = [];
        $input = new ShopGetDataDto($groupId, $shopsId, $shopsId, $shopNameStartsWith, $shopName);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with(null, null, null, $shopName, null)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGetShopsDataNoShopsFound(): void
    {
        $shopNameStartsWith = null;
        $shopName = ValueObjectFactory::createNameWithSpaces(null);
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $input = new ShopGetDataDto($groupId, $shopsId, $shopsId, $shopNameStartsWith, $shopName);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($shopsId, $groupId, $shopsId, $shopName, $shopNameStartsWith)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
