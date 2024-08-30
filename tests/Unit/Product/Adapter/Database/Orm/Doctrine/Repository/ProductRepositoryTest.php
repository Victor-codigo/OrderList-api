<?php

declare(strict_types=1);

namespace Test\Unit\Product\Adapter\Database\Orm\Doctrine\Repository;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Group\Domain\Model\Group;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Product\Adapter\Database\Orm\Doctrine\Repository\ProductRepository;
use Product\Domain\Model\Product;
use Test\Unit\DataBaseTestCase;

class ProductRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_ID_2 = 'faeb885d-d6c4-466d-ac4a-793d87e03e86';
    private const string SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const string PRODUCT_ID = 'afc62bc9-c42c-4c4d-8098-09ce51414a92';
    private const string PRODUCT_ID_2 = '8b6d650b-7bb7-4850-bf25-36cda9bce801';
    private const string PRODUCT_ID_3 = 'ca10c90a-c7e6-4594-89e9-71d2f5e74710';
    private const string PRODUCT_ID_4 = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';

    private ProductRepository $object;
    private MockObject|ConnectionException $connectionException;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Product::class);
        $this->connectionException = $this->createMock(ConnectionException::class);
    }

    private function getNewProduct(): Product
    {
        return Product::fromPrimitives(
            'ddb1a26e-ed57-4e83-bc5c-0ee6f6f5c8f8',
            self::GROUP_ID,
            'product 1 name',
            'Product 1 description',
            null
        );
    }

    private function getExistsProduct(): Product
    {
        return Product::fromPrimitives(
            'afc62bc9-c42c-4c4d-8098-09ce51414a92',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Sabryna Kuhn',
            'Nam tenetur veniam sapiente eum. Explicabo necessitatibus beatae inventore quibusdam. Odit aut non facere facere quisquam alias repellat. Velit vitae sed ut deserunt impedit ea. Id sed eos hic sint reiciendis similique et labore. Velit est perferendis tenetur possimus nobis aliquid soluta. Molestias alias corrupti sequi. Quis sit autem voluptatibus odit voluptate amet. Quibusdam voluptas in quia magni doloribus vel ut. Voluptas et soluta rem aliquam voluptates pariatur et sed.',
            null,
        );
    }

    #[Test]
    public function itShouldSaveTheProductInDatabase(): void
    {
        $productNew = $this->getNewProduct();
        $this->object->save($productNew);
        /** @var Group $productSaved */
        $productSaved = $this->object->findOneBy(['id' => $productNew->getId()]);

        $this->assertSame($productNew, $productSaved);
    }

    #[Test]
    public function itShouldFailProductIdAlreadyExists(): void
    {
        $this->expectException(DBUniqueConstraintException::class);

        $this->object->save($this->getExistsProduct());
    }

    #[Test]
    public function itShouldFailSavingDataBaseError(): void
    {
        $this->expectException(DBConnectionException::class);

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($this->connectionException);

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->save($this->getNewProduct());
    }

    #[Test]
    public function itShouldRemoveTheProduct(): void
    {
        $product = $this->getExistsProduct();
        $productToRemove = $this->object->findBy(['id' => $product->getId()]);
        $this->object->remove($productToRemove);

        $productRemoved = $this->object->findBy(['id' => $product->getId()]);

        $this->assertEmpty($productRemoved);
    }

    #[Test]
    public function itShouldFailRemovingTheProductErrorConnection(): void
    {
        $this->expectException(DBConnectionException::class);

        $product = $this->getNewProduct();

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($this->connectionException);

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove([$product]);
    }

    #[Test]
    public function itShouldGetTheProductsOfAGroupWhitAName(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Juanola');
        $return = $this->object->findProductsByGroupAndNameOrFail($groupId, $productName);

        $this->assertCount(1, $return);

        foreach ($return as $product) {
            $this->assertEquals($groupId, $product->getGroupId());
        }
    }

    #[Test]
    public function itShouldFailGettingTheProductOfAGroupWithANameNoProduct(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('product that not exists');

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsByGroupAndNameOrFail($groupId, $productName);
    }

    #[Test]
    public function itShouldGetTheProductsOfAGroupWhitANameAndProductNameStartsWith(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Juanola');
        $return = $this->object->findProductsByGroupAndNameOrFail($groupId, $productName);

        $this->assertCount(1, $return);

        foreach ($return as $product) {
            $this->assertEquals($groupId, $product->getGroupId());
        }
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupOrderAsc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findProductsOrFail($groupId);

        $this->assertCount(4, $return);

        foreach ($return as $product) {
            $this->assertEquals($product->getGroupId(), $groupId);
        }
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupProductsIdOrderAsc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productIds = [
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID_2),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID_3),
        ];

        $return = $this->object->findProductsOrFail($groupId, $productIds);

        $this->assertCount(3, $return);

        foreach ($return as $product) {
            $this->assertContainsEquals($product->getId(), $productIds);
        }
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupProductsIdOrderDesc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productIds = [
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID_3),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID_2),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
        ];

        $return = $this->object->findProductsOrFail(groupId: $groupId, productsId: $productIds, orderAsc: false);

        $this->assertCount(3, $return);

        foreach ($return as $product) {
            $this->assertContainsEquals($product->getId(), $productIds);
        }
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupAndAShopsIdOrderAsc(): void
    {
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findProductsOrFail($groupId, null, [$shopId]);

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID,
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    #[Test]
    public function itShouldFailGettingAllProductsOfAGroupNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('fc4f5962-02d1-4d80-b3ae-8aeffbcb6268');

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsOrFail($groupId);
    }

    #[Test]
    public function itShouldGetProductsOfAGroupAndProductNameOrderAsc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Juanola');
        $return = $this->object->findProductsByProductNameOrFail($groupId, $productName);

        $this->assertCount(1, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    #[Test]
    public function itShouldGetProductsOfAGroupWithProductNameOrderDesc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Juanola');
        $return = $this->object->findProductsByProductNameOrFail($groupId, $productName, false);

        $this->assertCount(1, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    #[Test]
    public function itShouldFailGettingProductsOfAGroupAndProductNameNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Not exists');

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsByProductNameOrFail($groupId, $productName);
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupWithProductNameFilterOrderAsc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productNameFilter = new Filter(
            'product_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces('Ju')
        );

        $return = $this->object->findProductsByProductNameFilterOrFail($groupId, $productNameFilter);

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_2,
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupWithProductNameFilterOrderDesc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productNameFilter = new Filter(
            'product_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces('Ju')
        );

        $return = $this->object->findProductsByProductNameFilterOrFail($groupId, $productNameFilter, false);

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_4,
            self::PRODUCT_ID_2,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    #[Test]
    public function itShouldFailGettingAllProductsOfAGroupWithProductNameFilterNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productNameFilter = new Filter(
            'product_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces('Not exists')
        );

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsByProductNameFilterOrFail($groupId, $productNameFilter);
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupWithShopNameFilterOrderAsc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNameFilter = new Filter(
            'product_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces('Shop name 3')
        );

        $return = $this->object->findProductsByShopNameFilterOrFail($groupId, $shopNameFilter);

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_4,
            self::PRODUCT_ID_3,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    #[Test]
    public function itShouldGetAllProductsOfAGroupWithShopNameFilterOrderDesc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNameFilter = new Filter(
            'shop_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces('Shop name 3')
        );

        $return = $this->object->findProductsByShopNameFilterOrFail($groupId, $shopNameFilter, false);

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_3,
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    #[Test]
    public function itShouldFailGettingAllProductsOfAGroupWithShopNameFilterNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNameFilter = new Filter(
            'product_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces('Not exists')
        );

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsByShopNameFilterOrFail($groupId, $shopNameFilter);
    }

    #[Test]
    public function itShouldFindAllProductsOfAGroups(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ];

        $return = $this->object->findGroupsProductsOrFail($groupsId);
        $expect = $this->object->findBy([
            'groupId' => $groupsId,
        ]);

        $this->assertEquals($expect, iterator_to_array($return));
    }

    #[Test]
    public function itShouldFailNoProductsFoundInGroups(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier('6f76bd87-7382-46fc-9746-ba983dbbfeba'),
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupsProductsOrFail($groupsId);
    }

    #[Test]
    public function itShouldGetFirstLetterOfAllProductsInAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $expected = ['j', 'm', 'p'];

        $return = $this->object->findGroupProductsFirstLetterOrFail($groupId);

        $this->assertEquals($expected, $return);
    }

    #[Test]
    public function itShouldFailGetFirstLetterOfAllProductsInAGroupNoProducts(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID_2);

        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupProductsFirstLetterOrFail($groupId);
    }
}
