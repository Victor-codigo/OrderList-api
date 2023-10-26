<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrdersGroupGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrdersGroupGetData\Dto\OrdersGroupGetDataInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrdersGroupGetDataInputDtoTest extends TestCase
{
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private MockObject|UserShared $userSession;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new OrdersGroupGetDataInputDto(
            $this->userSession,
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new OrdersGroupGetDataInputDto(
            $this->userSession,
            null,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new OrdersGroupGetDataInputDto(
            $this->userSession,
            'group id wrong',
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $object = new OrdersGroupGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsWrong(): void
    {
        $object = new OrdersGroupGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            -1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new OrdersGroupGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            1,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsWrong(): void
    {
        $object = new OrdersGroupGetDataInputDto(
            $this->userSession,
            self::GROUP_ID,
            1,
            -1
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}
