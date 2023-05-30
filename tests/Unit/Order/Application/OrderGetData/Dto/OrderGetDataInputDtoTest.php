<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderGetData\Dto\OrderGetDataInputDto;
use PHPUnit\Framework\TestCase;

class OrderGetDataInputDtoTest extends TestCase
{
    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const ORDERS_ID = [
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
        'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
        'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
    ];

    private ValidationInterface $validator;
    private UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new OrderGetDataInputDto($this->userSession, self::ORDERS_ID, self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailOrdersIdIsNull(): void
    {
        $object = new OrderGetDataInputDto($this->userSession, null, self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailOrdersIdIsEmpty(): void
    {
        $object = new OrderGetDataInputDto($this->userSession, [], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailOrdersIdIsWrong(): void
    {
        $object = new OrderGetDataInputDto($this->userSession, ['wrong id'], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new OrderGetDataInputDto($this->userSession, self::ORDERS_ID, null);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new OrderGetDataInputDto($this->userSession, self::ORDERS_ID, 'wrong id');

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailOrderIsWrongGroupIdIsWrong(): void
    {
        $object = new OrderGetDataInputDto($this->userSession, ['wrong id'], 'wrong id');

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
                'orders_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
            ],
            $return
        );
    }
}
