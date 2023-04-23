<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\VALIDATION_ERRORS;
use PHPUnit\Framework\TestCase;

class NotificationTypeTest extends TestCase
{
    private ValidationChain $validation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheGUserRegisteredType(): void
    {
        $object = new NotificationType(NOTIFICATION_TYPE::USER_REGISTERED);
        $return = $this->validation->validateValueObject($object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailIsNull(): void
    {
        $object = new NotificationType(null);
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    /** @test */
    public function itShouldFailIsNotNotificationType(): void
    {
        $object = new NotificationType(new \stdClass());
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }
}
