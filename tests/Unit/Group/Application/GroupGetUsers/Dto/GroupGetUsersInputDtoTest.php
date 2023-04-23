<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetUsers\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupGetUsersInputDtoTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private MockObject|User $userSession;
    private MockObject|ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheInput(): void
    {
        $object = new GroupGetUsersInputDto($this->userSession, self::GROUP_ID, 1, 5);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidatePageItemsIs1(): void
    {
        $object = new GroupGetUsersInputDto($this->userSession, self::GROUP_ID, 5, 1);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new GroupGetUsersInputDto($this->userSession, null, 1, 5);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldValidatePageIs1(): void
    {
        $object = new GroupGetUsersInputDto($this->userSession, self::GROUP_ID, 1, 1);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $groupId = 'not valid id';
        $object = new GroupGetUsersInputDto($this->userSession, $groupId, 1, 5);
        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsLessThanOne(): void
    {
        $object = new GroupGetUsersInputDto($this->userSession, self::GROUP_ID, 5, 0);
        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsGreaterThan100(): void
    {
        $object = new GroupGetUsersInputDto($this->userSession, self::GROUP_ID, 5, 101);
        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::LESS_THAN_OR_EQUAL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsLessThanOne(): void
    {
        $object = new GroupGetUsersInputDto($this->userSession, self::GROUP_ID, 0, 1);
        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}
