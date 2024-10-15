<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersCreateFrom\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersCreateFrom\Dto\ListOrdersCreateFromInputDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersCreateFromInputDtoTest extends TestCase
{
    private const string LIST_ORDERS_ID_CREATE_FROM = '466acd12-e6f9-4d5f-9c36-dda35ea45b05';
    private const string GROUP_ID = 'c593d795-72a1-4d29-8b1a-3ae6cdb6dcf8';
    private ValidationInterface $validator;
    private MockObject&UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ListOrdersCreateFromInputDto(
            $this->userSession,
            self::LIST_ORDERS_ID_CREATE_FROM,
            self::GROUP_ID,
            'list orders name'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailValidatingListOrdersIdIsNull(): void
    {
        $object = new ListOrdersCreateFromInputDto(
            $this->userSession,
            null,
            self::GROUP_ID,
            'list orders name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id_create_from' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingListOrdersIdIsWrong(): void
    {
        $object = new ListOrdersCreateFromInputDto(
            $this->userSession,
            'wrong id',
            self::GROUP_ID,
            'list orders name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id_create_from' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailValidatingGroupIdIsNull(): void
    {
        $object = new ListOrdersCreateFromInputDto(
            $this->userSession,
            self::LIST_ORDERS_ID_CREATE_FROM,
            null,
            'list orders name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingGroupIdIsWrong(): void
    {
        $object = new ListOrdersCreateFromInputDto(
            $this->userSession,
            self::LIST_ORDERS_ID_CREATE_FROM,
            'wrong id',
            'list orders name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailValidatingNameIdIsNull(): void
    {
        $object = new ListOrdersCreateFromInputDto(
            $this->userSession,
            self::LIST_ORDERS_ID_CREATE_FROM,
            self::GROUP_ID,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingNameIdIsWrong(): void
    {
        $object = new ListOrdersCreateFromInputDto(
            $this->userSession,
            self::LIST_ORDERS_ID_CREATE_FROM,
            self::GROUP_ID,
            'list-orders-name'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }
}
