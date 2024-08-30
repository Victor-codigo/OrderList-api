<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetDataByName\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetDataByName\Dto\GroupGetDataByNameInputDto;
use PHPUnit\Framework\TestCase;

class GroupGetDataByNameInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $groupName = 'groupName';
        $object = new GroupGetDataByNameInputDto($this->userSession, $groupName);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailGroupNameIsNull(): void
    {
        $groupName = null;
        $object = new GroupGetDataByNameInputDto($this->userSession, $groupName);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupNameIsWrong(): void
    {
        $groupName = 'not valid name-';
        $object = new GroupGetDataByNameInputDto($this->userSession, $groupName);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }
}
