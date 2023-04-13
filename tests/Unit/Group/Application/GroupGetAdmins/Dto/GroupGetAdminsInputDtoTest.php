<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetAdmins\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetAdmins\Dto\GroupGetAdminsInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupGetAdminsInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|User $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userSession = $this->createMock(User::class);
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $groupId = 'dad79f1c-52a8-4cf7-812b-62fc8bff7043';
        $object = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupId = null;
        $object = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $groupId = 'group id wrong';
        $object = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
