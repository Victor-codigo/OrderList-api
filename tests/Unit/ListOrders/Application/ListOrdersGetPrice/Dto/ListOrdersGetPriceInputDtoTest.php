<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersGetPrice\Dto;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetPrice\Dto\ListOrdersGetPriceInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersGetPriceInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ListOrdersGetPriceInputDto(
            $this->userSession,
            'c12f772a-f7c5-448a-8998-cd2d8a218369',
            'a11309bf-62f3-4bd6-8340-3695adc4b967',
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $object = new ListOrdersGetPriceInputDto(
            $this->userSession,
            null,
            'a11309bf-62f3-4bd6-8340-3695adc4b967',
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $object = new ListOrdersGetPriceInputDto(
            $this->userSession,
            'wrong id',
            'a11309bf-62f3-4bd6-8340-3695adc4b967',
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ListOrdersGetPriceInputDto(
            $this->userSession,
            'a11309bf-62f3-4bd6-8340-3695adc4b967',
            null,
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ListOrdersGetPriceInputDto(
            $this->userSession,
            'a11309bf-62f3-4bd6-8340-3695adc4b967',
            'wrong id',
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
