<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetAdmins\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetAdmins\Dto\GroupGetAdminsInputDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupGetAdminsInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject&UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userSession = $this->createMock(UserShared::class);
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $groupId = 'dad79f1c-52a8-4cf7-812b-62fc8bff7043';
        $object = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupId = null;
        $object = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupIdIsWrong(): void
    {
        $groupId = 'group id wrong';
        $object = new GroupGetAdminsInputDto($this->userSession, $groupId);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
