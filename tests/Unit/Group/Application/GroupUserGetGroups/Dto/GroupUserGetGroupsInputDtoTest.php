<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserGetGroups\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserGetGroups\Dto\GroupUserGetGroupsInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupUserGetGroupsInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|User $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateAlways(): void
    {
        $object = new GroupUserGetGroupsInputDto($this->userSession);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }
}
