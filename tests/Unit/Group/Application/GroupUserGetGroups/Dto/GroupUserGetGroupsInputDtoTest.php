<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserGetGroups\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
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
    public function itShouldValidate(): void
    {
        $object = new GroupUserGetGroupsInputDto($this->userSession, 1, 100);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $object = new GroupUserGetGroupsInputDto($this->userSession, null, 100);
        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsLowerThanOne(): void
    {
        $object = new GroupUserGetGroupsInputDto($this->userSession, 0, 100);
        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new GroupUserGetGroupsInputDto($this->userSession, 1, null);
        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsLowerThanOne(): void
    {
        $object = new GroupUserGetGroupsInputDto($this->userSession, 1, 0);
        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}
