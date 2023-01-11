<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserRemove\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Application\UserRemove\Dto\UserRemoveInputDto;
use User\Domain\Model\User;

class UserRemoveInputDtoTest extends TestCase
{
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const USER_ID_WRONG = '2606508b-4516-45d6-93a6-c7cb416-wrong';

    private ValidationInterface $validator;
    private MockObject|User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new UserRemoveInputDto($this->user, self::USER_ID);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailUserIdIsWrong(): void
    {
        $object = new UserRemoveInputDto($this->user, self::USER_ID_WRONG);
        $return = $object->validate($this->validator);

        $this->assertEquals(['id' => [VALIDATION_ERRORS::UUID_INVALID_HYPHEN_PLACEMENT]], $return);
    }
}
