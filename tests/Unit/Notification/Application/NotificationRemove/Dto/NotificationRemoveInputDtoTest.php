<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Application\NotificationRemove\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationRemove\Dto\NotificationRemoveInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationRemoveInputDtoTest extends TestCase
{
    private const NOTIFICATION_ID_1 = '2d208936-a7e9-32c1-963f-0df7f57ae463';
    private const NOTIFICATION_ID_2 = '38dac117-2d4f-4057-8bc6-c972b5f439c6';
    private const NOTIFICATION_ID_3 = '79a674c7-e109-3094-b8d5-c19cc00f5519';

    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    private function getNotificationsIds(): array
    {
        return [
            ValueObjectFactory::createIdentifier(self::NOTIFICATION_ID_1),
            ValueObjectFactory::createIdentifier(self::NOTIFICATION_ID_2),
            ValueObjectFactory::createIdentifier(self::NOTIFICATION_ID_3),
        ];
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $notificationsIds = $this->getNotificationsIds();
        $object = new NotificationRemoveInputDto($this->userSession, $notificationsIds);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNotificationsAreEmpty(): void
    {
        $notificationsIds = null;
        $object = new NotificationRemoveInputDto($this->userSession, $notificationsIds);
        $return = $object->validate($this->validator);

        $this->assertEquals(['notifications_id' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailNotificationsIdsNotValid(): void
    {
        $notificationsIds = $this->getNotificationsIds();
        $notificationsIds[] = 'not valid notification id';
        $object = new NotificationRemoveInputDto($this->userSession, $notificationsIds);
        $return = $object->validate($this->validator);

        $this->assertEquals(['notifications_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
