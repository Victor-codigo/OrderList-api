<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserGetByNAme\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\UserGetByName\Dto\UserGetByNameInputDto;
use User\Domain\Model\User;

class UserGetByNameInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private User $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $userName = 'Juan';
        $object = new UserGetByNameInputDto($this->userSession, [$userName]);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateManyNames(): void
    {
        $userName = ['Juan', 'Pedro', 'Ana', 'José María'];
        $object = new UserGetByNameInputDto($this->userSession, $userName);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailNotValidUserName(): void
    {
        $userName = 'Juan y Medio+';
        $object = new UserGetByNameInputDto($this->userSession, [$userName]);
        $return = $object->validate($this->validator);

        $this->assertEquals(['users_name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    #[Test]
    public function itShouldFailUserNameIsNull(): void
    {
        $userName = null;
        $object = new UserGetByNameInputDto($this->userSession, $userName);
        $return = $object->validate($this->validator);

        $this->assertEquals(['users_name' => [VALIDATION_ERRORS::NOT_NULL, VALIDATION_ERRORS::NOT_BLANK]], $return);
    }
}
