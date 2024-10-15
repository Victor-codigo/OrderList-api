<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Application\NotificationCreate\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationCreate\Dto\NotificationCreateInputDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationCreateInputDtoTest extends TestCase
{
    private const string USER_ID = '38dac117-2d4f-4057-8bc6-c972b5f439c6';
    private const string SYSTEM_KEY = 'system key';

    private ValidationInterface $validator;
    private MockObject&UserShared $user;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /**
     * @return array<string, string|int>
     */
    private function getNotificationData(): array
    {
        return [
            'data1' => 1,
            'data2' => 'value 2',
            'data3' => 'value 3',
        ];
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            $this->getNotificationData(),
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateManyUsersId(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [
                self::USER_ID,
                'a57cbab6-7611-4138-a7c2-0306403d89d9',
                'e0d0a1d7-1e67-4817-888c-3c9c1ea27ff4',
            ],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            $this->getNotificationData(),
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailUserIdIsNull(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            null,
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            $this->getNotificationData(),
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailSystemKeyIsNull(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            $this->getNotificationData(),
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['system_key' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailUserIdINotValidIdAndTypeNotValid(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            ['not a valid id'],
            'not valid type',
            $this->getNotificationData(),
            ''
        );

        $return = $object->validate($this->validator);

        $this->assertEquals([
            'users_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            'type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            'system_key' => [VALIDATION_ERRORS::NOT_BLANK],
        ],
            $return
        );
    }

    #[Test]
    public function itShouldFailUserIdINotValidId(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            ['not a valid id'],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            $this->getNotificationData(),
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailManyUserIdINotValidId(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [
                self::USER_ID,
                'not a valid id',
                'not a valid id',
            ],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            $this->getNotificationData(),
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailNotificationTypeIsNull(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            null,
            $this->getNotificationData(),
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailNotificationTypeIsNotValid(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            'not valid notification',
            $this->getNotificationData(),
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailNotificationDataIsNull(): void
    {
        $object = new NotificationCreateInputDto(
            $this->user,
            [self::USER_ID],
            NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            null,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['notification_data' => [VALIDATION_ERRORS::NOT_NULL]], $return);
    }
}
