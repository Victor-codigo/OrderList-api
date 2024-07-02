<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopRemove\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopRemove\Dto\ShopRemoveInputDto;

class ShopRemoveInputDtoTest extends TestCase
{
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';

    private ValidationInterface $validator;
    private MockObject|UserShared $userShared;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userShared = $this->createMock(UserShared::class);
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = self::SHOP_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, [$shopId]);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateManyShopsId(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = [
            self::SHOP_ID,
            self::SHOP_ID,
            self::SHOP_ID,
        ];
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupId = null;
        $shopId = self::SHOP_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, [$shopId]);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $groupId = 'wrong group id';
        $shopId = self::SHOP_ID;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, [$shopId]);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = null;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $shopId = 'shop id wrong';
        $object = new ShopRemoveInputDto($this->userShared, $groupId, [$shopId]);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailSomeShopIdAreWrong(): void
    {
        $groupId = self::GROUP_ID;
        $shopsId = [
            self::SHOP_ID,
            'shop id wrong',
            self::SHOP_ID,
        ];
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopsId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['shops_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdShopShopIdAreNull(): void
    {
        $groupId = null;
        $shopId = null;
        $object = new ShopRemoveInputDto($this->userShared, $groupId, $shopId);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'shops_id_empty' => [VALIDATION_ERRORS::NOT_BLANK],
            ],
            $return
        );
    }
}
