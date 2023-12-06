<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Service\ShopGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
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
    public function itShouldGetShopsDataOrderByShopIdAndProductIdAsc(): void
    {
        $shops = $this->getShops();
        $input = new ShopGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [ValueObjectFactory::createIdentifier('shop 1 id')],
            [ValueObjectFactory::createIdentifier('product 1 id')],
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            ValueObjectFactory::createNameWithSpaces('Shop name'),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100),
            true,
        );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with(
                $input->groupId,
                $input->shopsId,
                $input->productsId,
                $input->orderAsc
            )
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameOrFail');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($shops));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($shops), $return);

        foreach ($shops as $key => $shopExpected) {
            $this->assertShopDataIsOk($shopExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetShopsDataOfAGroupOrderByNameAsc(): void
    {
        $shops = $this->getShops();
        $input = new ShopGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [],
            [],
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            ValueObjectFactory::createNameWithSpaces(null),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100),
            true,
        );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($input->groupId, null, null, $input->orderAsc)
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameOrFail');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array_reverse($shops)));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($shops), $return);

        foreach (array_reverse($shops) as $key => $shopExpected) {
            $this->assertShopDataIsOk($shopExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetShopsDataByShopNameOrderByNameAsc(): void
    {
        $shops = $this->getShops();
        $input = new ShopGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [],
            [],
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            ValueObjectFactory::createNameWithSpaces('Shop name'),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100),
            true,
        );

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameOrFail')
            ->with(
                $input->groupId,
                $input->shopName,
                $input->orderAsc
            )
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($shops));

        $return = $this->object->__invoke($input);

        $this->assertCount(count($shops), $return);

        foreach ($shops as $key => $shopExpected) {
            $this->assertShopDataIsOk($shopExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetShopsDataWithFilterOrderByNameAsc(): void
    {
        $shops = $this->getShops();
        $input = new ShopGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [],
            [],
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces('Shop')
            ),
            ValueObjectFactory::createNameWithSpaces(null),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100),
            true,
        );

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameOrFail');

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopByShopNameFilterOrFail')
            ->with(
                $input->groupId,
                $input->shopNameFilter,
                $input->orderAsc
            )
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

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
    public function itShouldGetShopsDataAllInputsAreNull(): void
    {
        $input = new ShopGetDataDto(
            ValueObjectFactory::createIdentifier(null),
            [],
            [],
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            ValueObjectFactory::createNameWithSpaces(null),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100),
            true
        );

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameOrFail');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopByShopNameFilterOrFail');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(LogicException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGetShopsDataNoShopsFound(): void
    {
        $input = new ShopGetDataDto(
            ValueObjectFactory::createIdentifier('group id'),
            [ValueObjectFactory::createIdentifier('shop 1 id')],
            [ValueObjectFactory::createIdentifier('shop 1 id')],
            new Filter(
                'shop_name',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            ValueObjectFactory::createNameWithSpaces(null),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100),
            true
        );

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with(
                $input->groupId,
                $input->shopsId,
                $input->productsId,
                $input->orderAsc
            )
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
