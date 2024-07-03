<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupRemove\Dto;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use PHPUnit\Framework\TestCase;

class GroupRemoveInputDtoTest extends TestCase
{
    private const string GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string GROUP_2_ID = 'a5002966-dbf7-4f76-a862-23a04b5ca465';

    private UserShared $userSession;
    private ValidationInterface $validator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateGroupId(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, [self::GROUP_ID]);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateGroupsId(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, [self::GROUP_ID, self::GROUP_2_ID]);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupsIdIsNull(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, null);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEquals(['groups_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailGroupsIdAreWrong(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, ['id not valid', 'other not valid id']);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEquals(['groups_id' => [
            [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
        ]],
            $return
        );
    }
}
