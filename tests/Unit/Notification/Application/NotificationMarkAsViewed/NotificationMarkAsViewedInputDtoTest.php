<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Application\NotificationMarkAsViewed;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedInputDto;
use PHPUnit\Framework\TestCase;

class NotificationMarkAsViewedInputDtoTest extends TestCase
{
    private UserShared $userSession;
    private ValidationInterface $validator;

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
        $object = new NotificationMarkAsViewedInputDto(
            $this->userSession, [
                'eb05e579-de82-42df-8acb-9244cb6a20fe',
                'f99682b5-9368-4a9c-8335-624bb17064ad',
                'a761b966-7946-4767-bb08-428d4489247d',
            ]
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNotificationsIdIsNull(): void
    {
        $object = new NotificationMarkAsViewedInputDto(
            $this->userSession,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['notifications_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailNotificationsIdIsEmpty(): void
    {
        $object = new NotificationMarkAsViewedInputDto(
            $this->userSession,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['notifications_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailNotificationsIdWrong(): void
    {
        $object = new NotificationMarkAsViewedInputDto(
            $this->userSession, [
                'notification 1 id',
                'notification 2 id',
                'notification 3 id',
            ]);

        $return = $object->validate($this->validator);

        $this->assertEquals(['notifications_id' => [
            [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
        ]],
            $return
        );
    }
}
