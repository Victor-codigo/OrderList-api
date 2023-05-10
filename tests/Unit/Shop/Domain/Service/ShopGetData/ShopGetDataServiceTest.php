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

    private function assertShopDataIsOk(array $shopsDataExpected, array $shopDataActual): void
    {
        $this->assertArrayHasKey('id', $shopDataActual);
        $this->assertArrayHasKey('group_id', $shopDataActual);
        $this->assertArrayHasKey('name', $shopDataActual);
        $this->assertArrayHasKey('description', $shopDataActual);
        $this->assertArrayHasKey('image', $shopDataActual);
        $this->assertArrayHasKey('created_on', $shopDataActual);

        $this->assertContainsEquals(
            $shopDataActual['id'],
            array_map(
                fn (Shop $shop) => $shop->getId()->getValue(),
                $shopsDataExpected
            )
        );
        $this->assertContainsEquals(
            $shopDataActual['group_id'],
            array_map(
                fn (Shop $shop) => $shop->getGroupId()->getValue(),
                $shopsDataExpected
            )
        );
        $this->assertContainsEquals(
            $shopDataActual['name'],
            array_map(
                fn (Shop $shop) => $shop->getName()->getValue(),
                $shopsDataExpected
            )
        );
        $this->assertContainsEquals(
            $shopDataActual['description'],
            array_map(
                fn (Shop $shop) => $shop->getDescription()->getValue(),
                $shopsDataExpected
            )
        );
        $this->assertContainsEquals(
            $shopDataActual['image'], array_map(
                fn (Shop $shop) => $shop->getImage()->getValue(),
                $shopsDataExpected
            )
        );
        $this->assertIsString($shopDataActual['created_on']);
    }

    /** @test */
    public function itShouldGetShopsData(): void
    {
        $shops = $this->getShops();
        $shopNameStartsWith = null;
        $shopsMaxNumber = 100;
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $productsId = [
            ValueObjectFactory::createIdentifier('product 1 id'),
        ];
        $input = new ShopGetDataDto($groupId, $shopsId, $productsId, $shopNameStartsWith);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($shopsId, $groupId, $productsId, $shopNameStartsWith)
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

        foreach ($return as $shop) {
            $this->assertShopDataIsOk($shops, $shop);
        }
    }

    /** @test */
    public function itShouldGetShopsDataAllInputsAreNull(): void
    {
        $shopNameStartsWith = null;
        $groupId = ValueObjectFactory::createIdentifier(null);
        $shopsId = [];
        $shopsId = [];
        $input = new ShopGetDataDto($groupId, $shopsId, $shopsId, $shopNameStartsWith);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with(null, null, null, null)
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
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $shopsId = [
            ValueObjectFactory::createIdentifier('shop 1 id'),
        ];
        $input = new ShopGetDataDto($groupId, $shopsId, $shopsId, $shopNameStartsWith);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($shopsId, $groupId, $shopsId, $shopNameStartsWith)
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
