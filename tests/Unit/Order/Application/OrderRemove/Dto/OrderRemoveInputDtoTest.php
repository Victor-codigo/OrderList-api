<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderRemove\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderRemove\Dto\OrderRemoveInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderRemoveInputDtoTest extends TestCase
{
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const array ORDERS_ID = [
        '9fc56488-e0e2-416e-b241-f8e7b868b0cb',
        '7dbcafd9-327a-46f4-8bc6-09614a26b1d7',
    ];

    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

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
        $object = new OrderRemoveInputDto($this->userSession, self::ORDERS_ID, self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new OrderRemoveInputDto($this->userSession, self::ORDERS_ID, null);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new OrderRemoveInputDto($this->userSession, self::ORDERS_ID, 'wrong id');

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailOrdersIsNull(): void
    {
        $object = new OrderRemoveInputDto($this->userSession, null, self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailOrdersIsEmpty(): void
    {
        $object = new OrderRemoveInputDto($this->userSession, [], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailOrdersIsWrong(): void
    {
        $object = new OrderRemoveInputDto($this->userSession, ['wrong id 1', 'wrong id 2'], self::GROUP_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['orders_id' => [
            [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
        ]],
            $return
        );
    }
}
