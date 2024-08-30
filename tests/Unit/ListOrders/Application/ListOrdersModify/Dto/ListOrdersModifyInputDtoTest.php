<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersModify\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersModify\Dto\ListOrdersModifyInputDto;
use PHPUnit\Framework\TestCase;

class ListOrdersModifyInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private UserShared $userSession;

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
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = 'list order name';
        $description = 'list order description';
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateDescriptionAndDateToBuyAreNull(): void
    {
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = 'list order name';
        $description = null;
        $dateToBuy = null;

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $listOrderId = null;
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = 'list order name';
        $description = 'list order description';
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $listOrderId = 'wrong id';
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = 'list order name';
        $description = 'list order description';
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersGroupIdIsNull(): void
    {
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = null;
        $name = 'list order name';
        $description = 'list order description';
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersGroupIdIsWrong(): void
    {
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = 'wrong id';
        $name = 'list order name';
        $description = 'list order description';
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersNameIsNull(): void
    {
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = null;
        $description = 'list order description';
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersNameIsWrong(): void
    {
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = str_pad('', 51, 'p');
        $description = 'list order description';
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersDescriptionIsWrong(): void
    {
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = 'list orders name';
        $description = str_pad('', 501, 'p');
        $dateToBuy = (new \DateTime())->format('Y-m-d H:i:s');

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersDateToBuyIsWrong(): void
    {
        $listOrderId = '83f65738-dce3-47c8-8081-d7cdb3647274';
        $groupId = '03a56202-32ab-43a9-9478-6e7ba7109ff1';
        $name = 'list orders name';
        $description = 'list orders description';
        $dateToBuy = 'wrong date';

        $object = new ListOrdersModifyInputDto(
            $this->userSession,
            $listOrderId,
            $groupId,
            $name,
            $description,
            $dateToBuy
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }
}
