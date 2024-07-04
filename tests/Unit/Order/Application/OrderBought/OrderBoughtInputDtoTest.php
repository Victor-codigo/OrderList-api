<?php

declare(strict_types=1);

namespace Test\Unit\Order\Application\OrderBought;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderBought\Dto\OrderBoughtInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderBoughtInputDtoTest extends TestCase
{
    private MockObject|UserShared $userSession;
    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userSession = $this->createMock(UserShared::class);
    }

    private function inputDataProvider(): iterable
    {
        $this->userSession = $this->createMock(UserShared::class);

        yield [
            $this->userSession,
            '47a79916-b7b8-4ff3-89f6-78279e6cc7fa',
            'f1112419-5118-413c-9c7a-2fa77e795b35',
            true,
        ];
        yield [
            $this->userSession,
            '7de5b898-5e5a-4394-9239-fb45f453f63e',
            'ae384dd0-1162-4cbb-be0a-eaefd12203c4',
            false,
        ];
    }

    /**
     * @test
     *
     * @dataProvider inputDataProvider
     */
    public function itShouldValidate(UserShared $userSession, ?string $orderId, ?string $groupId, ?bool $bought): void
    {
        $object = new OrderBoughtInputDto($userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailValidatingOrderIdIsNull(): void
    {
        $orderId = null;
        $groupId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $bought = true;
        $object = new OrderBoughtInputDto($this->userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailValidatingOrderIdIsWrong(): void
    {
        $orderId = 'wrong id';
        $groupId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $bought = true;
        $object = new OrderBoughtInputDto($this->userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['order_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailValidatingGroupIdIsNull(): void
    {
        $orderId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $groupId = null;
        $bought = true;
        $object = new OrderBoughtInputDto($this->userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailValidatingGroupIdIsWrong(): void
    {
        $orderId = '7de5b898-5e5a-4394-9239-fb45f453f63e';
        $groupId = 'wrong id';
        $bought = true;
        $object = new OrderBoughtInputDto($this->userSession, $orderId, $groupId, $bought);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
