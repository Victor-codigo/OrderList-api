<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Application\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationCreate\Dto\NotificationCreateInputDto;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class NotificationCreateInputDtoTest extends TestCase
{
    private const USER_ID = '38dac117-2d4f-4057-8bc6-c972b5f439c6';

    private ValidationInterface $validator;
    private MockObject|User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateManyUsersId(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [
                self::USER_ID,
                'a57cbab6-7611-4138-a7c2-0306403d89d9',
                'e0d0a1d7-1e67-4817-888c-3c9c1ea27ff4',
            ],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailUserIdIsNull(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            null,
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailUserIdINotValidIdAndTypeNotValid(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            ['not a valid id'],
            'not valid type'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
            'users_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            'type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
        ],
            $return
        );
    }

    /** @test */
    public function itShouldFailUserIdINotValidId(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            ['not a valid id'],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailManyUserIdINotValidId(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [
                self::USER_ID,
                'not a valid id',
                'not a valid id',
            ],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNotificationTypeIsNull(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNotificationTypeIsNotValid(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            'not valid notification'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }
}
