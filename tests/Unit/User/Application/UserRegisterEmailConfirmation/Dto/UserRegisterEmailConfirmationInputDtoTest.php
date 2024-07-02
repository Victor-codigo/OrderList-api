<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserRegisterEmailConfirmation\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\UserRegisterEmailConfirmation\Dto\UserEmailConfirmationInputDto;

class UserRegisterEmailConfirmationInputDtoTest extends TestCase
{
    private const int TOKEN_MIN_LENGTH = 36;

    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheToken(): void
    {
        $userEmailConfirmationInputDto = new UserEmailConfirmationInputDto(str_pad('', self::TOKEN_MIN_LENGTH, '-'));
        $return = $userEmailConfirmationInputDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailTokenTooShort(): void
    {
        $userEmailConfirmationInputDto = new UserEmailConfirmationInputDto(str_pad('', self::TOKEN_MIN_LENGTH - 1, '-'));
        $return = $userEmailConfirmationInputDto->validate($this->validator);

        $this->assertEquals(['token' => [VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }
}
