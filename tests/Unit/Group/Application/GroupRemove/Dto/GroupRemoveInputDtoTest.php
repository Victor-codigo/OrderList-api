<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupRemove\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use PHPUnit\Framework\TestCase;

class GroupRemoveInputDtoTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private UserShared $userSession;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheData(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, self::GROUP_ID);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, null);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWorng(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, 'id not valid');
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
