<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserGetByNAme\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\UserGetByName\Dto\UserGetByNameInputDto;
use User\Domain\Model\User;

class UserGetByNAmeInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private User $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $userName = 'Juan';
        $object = new UserGetByNameInputDto($this->userSession, $userName);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNotValidUserName(): void
    {
        $userName = 'Juan y Medio';
        $object = new UserGetByNameInputDto($this->userSession, $userName);
        $return = $object->validate($this->validator);

        $this->assertEquals(['user_name' => [VALIDATION_ERRORS::ALPHANUMERIC]], $return);
    }
}
