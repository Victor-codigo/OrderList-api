<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupRemove;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupRemoveInputDtoTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private User $userSession;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itSouldValidateTheData(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, self::GROUP_ID);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itSouldFailGroupIdIsNull(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, null);
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itSouldFailGroupIdIsWorng(): void
    {
        $groupRemoveInputDto = new GroupRemoveInputDto($this->userSession, 'id not valid');
        $return = $groupRemoveInputDto->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
