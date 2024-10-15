<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupRemoveAllUserGroups\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsInputDto;
use Notification\Application\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsInputDto as DtoNotificationRemoveAllUserNotificationsInputDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupRemoveAllUserGroupsInputDtoTest extends TestCase
{
    private const string SYSTEM_KEY = 'systemKeyForDev';

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
        $object = new GroupRemoveAllUserGroupsInputDto(
            $this->userSession,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailSystemKeyIsNull(): void
    {
        $object = new DtoNotificationRemoveAllUserNotificationsInputDto(
            $this->userSession,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['system_key' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }
}
