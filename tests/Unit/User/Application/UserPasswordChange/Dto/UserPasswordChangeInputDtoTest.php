<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserPasswordChange\Dto;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeInputDto;
use User\Domain\Model\User;

class UserPasswordChangeInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|User $userSession;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldPassValidation(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'passwordOld',
            'passwordNew',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailIdIsTooShort(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3',
            'passwordOld',
            'passwordNew',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['id' => [VALIDATION_ERRORS::UUID_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailIdIsTooLong(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f3',
            'passwordOld',
            'passwordNew',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['id' => [VALIDATION_ERRORS::UUID_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailIdIsNull(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            null,
            'passwordOld',
            'passwordNew',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPasswordOldTooShort(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'pass5',
            'passwordNew',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_old' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailPasswordOldTooLong(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            str_pad('', 51, '-'),
            'passwordNew',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_old' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailPasswordOldIsNull(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            null,
            'passwordNew',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_old' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewTooShort(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'passwordOld',
            'pass5',
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_new' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewTooLong(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'passwordOld',
            str_pad('', 51, '-'),
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_new' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewIsNull(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'passwordOld',
            null,
            'passwordNewRepeat'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_new' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatTooShort(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'passwordOld',
            'passwordNew',
            'pass5'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_new_repeat' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatTooLong(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'passwordOld',
            'passwordNew',
            str_pad('', 51, '-'),
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_new_repeat' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatIsNull(): void
    {
        $object = new UserPasswordChangeInputDto(
            $this->userSession,
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            'passwordOld',
            'passwordNew',
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['password_new_repeat' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }
}
