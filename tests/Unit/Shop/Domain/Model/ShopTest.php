<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Shop\Domain\Model\Shop;

class ShopTest extends TestCase
{
    #[Test]
    public function itShouldCreateAShopFromArray(): void
    {
        $shopData = [
            'id' => 'shop id',
            'group_id' => 'group_id',
            'name' => 'shop name',
            'address' => 'shop address',
            'description' => 'shop description',
            'image' => 'shop image',
        ];

        $return = Shop::fromPrimitiveArrayOfData($shopData);

        $this->assertEquals($shopData['id'], $return->getId()->getValue());
        $this->assertEquals($shopData['group_id'], $return->getGroupId()->getValue());
        $this->assertEquals($shopData['name'], $return->getName()->getValue());
        $this->assertEquals($shopData['address'], $return->getAddress()->getValue());
        $this->assertEquals($shopData['description'], $return->getDescription()->getValue());
        $this->assertEquals($shopData['image'], $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldCreateAShopFromArrayAddressNotExists(): void
    {
        $shopData = [
            'id' => 'shop id',
            'group_id' => 'group_id',
            'name' => 'shop name',
            'description' => 'shop description',
            'image' => 'shop image',
        ];

        $return = Shop::fromPrimitiveArrayOfData($shopData);

        $this->assertEquals($shopData['id'], $return->getId()->getValue());
        $this->assertEquals($shopData['group_id'], $return->getGroupId()->getValue());
        $this->assertEquals($shopData['name'], $return->getName()->getValue());
        $this->assertEquals(null, $return->getAddress()->getValue());
        $this->assertEquals($shopData['description'], $return->getDescription()->getValue());
        $this->assertEquals($shopData['image'], $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldCreateAShopFromArrayDescriptionNotExists(): void
    {
        $shopData = [
            'id' => 'shop id',
            'group_id' => 'group_id',
            'name' => 'shop name',
            'address' => 'shop address',
            'image' => 'shop image',
        ];

        $return = Shop::fromPrimitiveArrayOfData($shopData);

        $this->assertEquals($shopData['id'], $return->getId()->getValue());
        $this->assertEquals($shopData['group_id'], $return->getGroupId()->getValue());
        $this->assertEquals($shopData['name'], $return->getName()->getValue());
        $this->assertEquals($shopData['address'], $return->getAddress()->getValue());
        $this->assertEquals(null, $return->getDescription()->getValue());
        $this->assertEquals($shopData['image'], $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldCreateAShopFromArrayImageNotExists(): void
    {
        $shopData = [
            'id' => 'shop id',
            'group_id' => 'group_id',
            'name' => 'shop name',
            'address' => 'shop address',
            'description' => 'shop description',
        ];

        $return = Shop::fromPrimitiveArrayOfData($shopData);

        $this->assertEquals($shopData['id'], $return->getId()->getValue());
        $this->assertEquals($shopData['group_id'], $return->getGroupId()->getValue());
        $this->assertEquals($shopData['name'], $return->getName()->getValue());
        $this->assertEquals($shopData['address'], $return->getAddress()->getValue());
        $this->assertEquals($shopData['description'], $return->getDescription()->getValue());
        $this->assertEquals(null, $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldFailCreatingAShopFromArrayIdNotExists(): void
    {
        $shopData = [
            'group_id' => 'group_id',
            'name' => 'shop name',
            'address' => 'shop address',
            'description' => 'shop description',
            'image' => 'shop image',
        ];

        $this->expectException(LogicException::class);
        Shop::fromPrimitiveArrayOfData($shopData);
    }

    #[Test]
    public function itShouldFailCreatingAShopFromArrayGroupIdNotExists(): void
    {
        $shopData = [
            'id' => 'shop id',
            'name' => 'shop name',
            'address' => 'shop address',
            'description' => 'shop description',
            'image' => 'shop image',
        ];

        $this->expectException(LogicException::class);
        Shop::fromPrimitiveArrayOfData($shopData);
    }

    #[Test]
    public function itShouldFailCreatingAShopFromArrayNameNotExists(): void
    {
        $shopData = [
            'id' => 'shop id',
            'group_id' => 'group_id',
            'address' => 'shop address',
            'description' => 'shop description',
            'image' => 'shop image',
        ];

        $this->expectException(LogicException::class);
        Shop::fromPrimitiveArrayOfData($shopData);
    }
}
