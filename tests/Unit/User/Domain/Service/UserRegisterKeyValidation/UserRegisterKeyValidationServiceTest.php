<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\UserRegisterKeyValidation;

use PHPUnit\Framework\TestCase;
use User\Domain\Service\UserRegisterKeyValidation\Dto\UserRegisterKeyValidationInputDto;
use User\Domain\Service\UserRegisterKeyValidation\UserRegisterKeyValidationService;

class UserRegisterKeyValidationServiceTest extends TestCase
{
    private const REGISTER_KEY = 'key register';
    private UserRegisterKeyValidationService $userRegisterKeyValidationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRegisterKeyValidationService = new UserRegisterKeyValidationService(self::REGISTER_KEY);
    }

    /** @test */
    public function itShouldValidateTheKey(): void
    {
        $registerValidation = new UserRegisterKeyValidationInputDto(self::REGISTER_KEY);
        $return = $this->userRegisterKeyValidationService->__invoke($registerValidation);

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldNotValidateTheKey(): void
    {
        $registerValidation = new UserRegisterKeyValidationInputDto('Another key');
        $return = $this->userRegisterKeyValidationService->__invoke($registerValidation);

        $this->assertFalse($return);
    }
}
