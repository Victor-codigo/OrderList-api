<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderBought;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderBought\Dto\OrderBoughtInputDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderBoughtInputDtoTest extends TestCase
{
    private static MockObject|UserShared $userSession;
    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        self::$userSession = $this->createMock(UserShared::class);
    }

    public static function inputDataProvider(): iterable
    {
        self::$userSession = self::createMock(UserShared::class);

        yield [
            self::$userSession,
            '47a79916-b7b8-4ff3-89f6-78279e6cc7fa',
            'f1112419-5118-413c-9c7a-2fa77e795b35',
            true,
        ];
        yield [
            self::$userSession,
            '7de5b898-5e5a-4394-9239-fb45f453f63e',
            'ae384dd0-1162-4cbb-be0a-eaefd12203c4',
            false,
        ];
    }

    #[DataProvider('inputDataProvider')]
    #[Test]
    public function itShouldValidate(UserShared $userSession, ?string $orderId, ?string $groupId, ?bool $bought): void
    {
        $object = new OrderBoughtInputDto($userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailValidatingOrderIdIsNull(): void
    {
        $orderId = null;
        $groupId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $bought = true;
        $object = new OrderBoughtInputDto(self::$userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingOrderIdIsWrong(): void
    {
        $orderId = 'wrong id';
        $groupId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $bought = true;
        $object = new OrderBoughtInputDto(self::$userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailValidatingGroupIdIsNull(): void
    {
        $orderId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $groupId = null;
        $bought = true;
        $object = new OrderBoughtInputDto(self::$userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidatingGroupIdIsWrong(): void
    {
        $orderId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $groupId = 'wrong id';
        $bought = true;
        $object = new OrderBoughtInputDto(self::$userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
