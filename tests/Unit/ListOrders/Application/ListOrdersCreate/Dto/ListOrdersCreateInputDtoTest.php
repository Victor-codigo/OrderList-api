<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\ListOrdersCreate\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersCreate\Dto\ListOrdersCreateInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListOrdersCreateInputDtoTest extends TestCase
{
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';

    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            'listOrder name',
            'listOrder description',
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDescriptionIsNull(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            'listOrder name',
            null,
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDateToBuyIsNull(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            'listOrder name',
            'listOrder description',
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDateToBuyIsWrong(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            'listOrder name',
            'listOrder description',
            'wrong date'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            null,
            'listOrder name',
            'listOrder description',
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            'wrong id',
            'listOrder name',
            'listOrder description',
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            'listOrder description',
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNameIsWrong(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            'wrong name!',
            'listOrder description',
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionTooLong(): void
    {
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            'listOrders name',
            str_pad('', 501, 'p'),
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailDateToBuyIsWrong(): void
    {
        $datetimeNow = new \DateTime();
        $object = new ListOrdersCreateInputDto(
            $this->userSession,
            self::GROUP_ID,
            'listOrders name',
            'listOrder description',
            (new \DateTimeImmutable())
                ->setTimestamp($datetimeNow->getTimestamp() - 3601)->format('Y-m-d H:i:s')
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['date_to_buy' => [VALIDATION_ERRORS::GREATER_THAN_OR_EQUAL]], $return);
    }
}
