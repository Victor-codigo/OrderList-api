<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopGetData\Dto\ShopGetDataInputDto;

class ShopGetDataInputDtoTest extends TestCase
{
    private const GROUP_ID = 'd511f745-22ef-4de8-933f-81e1ffcda810';
    private const PRODUCTS_ID = [
        'd511f745-22ef-4de8-933f-81e1ffcda810',
        'eb221850-c5d1-4cb2-939d-d89d2f732db1',
    ];
    private const SHOPS_ID = [
        'd511f745-22ef-4de8-933f-81e1ffcda810',
        'f0872de3-bc35-4572-b69f-2c9bfd28f220',
    ];

    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $shopNameStartsWith = 'jp';
        $object = new ShopGetDataInputDto(self::GROUP_ID, self::SHOPS_ID, self::PRODUCTS_ID, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopsIdIsNull(): void
    {
        $shopNameStartsWith = 'jp';
        $object = new ShopGetDataInputDto(self::GROUP_ID, null, self::PRODUCTS_ID, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateProductsIdIsNull(): void
    {
        $shopNameStartsWith = 'jp';
        $object = new ShopGetDataInputDto(self::GROUP_ID, self::SHOPS_ID, null, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateShopNameStartsWithIsNull(): void
    {
        $object = new ShopGetDataInputDto(self::GROUP_ID, self::SHOPS_ID, self::PRODUCTS_ID, null);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $shopNameStartsWith = 'jp';
        $object = new ShopGetDataInputDto(null, self::SHOPS_ID, self::PRODUCTS_ID, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $shopNameStartsWith = 'jp';
        $object = new ShopGetDataInputDto('wrong id', self::SHOPS_ID, self::PRODUCTS_ID, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailShopsIdAreWrong(): void
    {
        $shopNameStartsWith = 'jp';
        $shopsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $object = new ShopGetDataInputDto(self::GROUP_ID, $shopsId, self::PRODUCTS_ID, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailProductsIdAreWrong(): void
    {
        $shopNameStartsWith = 'jp';
        $productsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $object = new ShopGetDataInputDto(self::GROUP_ID, self::SHOPS_ID, $productsId, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEquals(['products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailShopNameStartsWith(): void
    {
        $shopNameStartsWith = str_pad('', 51, 'p');
        $object = new ShopGetDataInputDto(self::GROUP_ID, self::SHOPS_ID, self::PRODUCTS_ID, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_name_starts_with' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailAllInputsAreWrong(): void
    {
        $shopNameStartsWith = str_pad('', 51, 'p');
        $groupId = 'wrong id';
        $shopsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $productsId = [
            'wrong id 1',
            'wrong id 2',
        ];
        $object = new ShopGetDataInputDto($groupId, $shopsId, $productsId, $shopNameStartsWith);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
                'shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
                'products_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
                'shop_name_starts_with' => [VALIDATION_ERRORS::STRING_TOO_LONG],
            ],
            $return
        );
    }
}
