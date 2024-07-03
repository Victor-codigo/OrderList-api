<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetData\Dto;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetData\Dto\GroupGetDataInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupGetDataInputDtoTest extends TestCase
{
    private const array GROUPS_ID = [
        'fdb242b4-bac8-4463-88d0-0941bb0beee0',
        'a5002966-dbf7-4f76-a862-23a04b5ca465',
        '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
    ];

    private ValidationInterface $validator;
    private MockObject|UserShared $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheInput(): void
    {
        $object = new GroupGetDataInputDto($this->user, self::GROUPS_ID);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupsIdIsNull(): void
    {
        $object = new GroupGetDataInputDto($this->user, null);
        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailGroupsIdIsEmpty(): void
    {
        $object = new GroupGetDataInputDto($this->user, []);
        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailGroupsIdAreWrong(): void
    {
        $groupsId = self::GROUPS_ID;
        $groupsId[] = 'not valid group id';
        $object = new GroupGetDataInputDto($this->user, $groupsId);
        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
