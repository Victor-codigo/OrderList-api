<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetGroupsAdmins\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetGroupsAdmins\Dto\GroupGetGroupsAdminsInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupGetGroupsAdminsInputDtoTest extends TestCase
{
    private GroupGetGroupsAdminsInputDto $object;
    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            [
                'a09ab66e-6413-4d30-8491-1ac491b3f12f',
                '4fd4dcc7-a5dc-4915-af4c-d64a06871906',
            ],
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupsIdIsEmpty(): void
    {
        $object = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            null,
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailGroupsIdIsWrong(): void
    {
        $object = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            ['wrong id'],
            1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $object = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            [
                'a09ab66e-6413-4d30-8491-1ac491b3f12f',
                '4fd4dcc7-a5dc-4915-af4c-d64a06871906',
            ],
            null,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsWrong(): void
    {
        $object = new GroupGetGroupsAdminsInputDto(
            $this->userSession, [
                'a09ab66e-6413-4d30-8491-1ac491b3f12f',
                '4fd4dcc7-a5dc-4915-af4c-d64a06871906',
            ],
            -1,
            100
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            [
                'a09ab66e-6413-4d30-8491-1ac491b3f12f',
                '4fd4dcc7-a5dc-4915-af4c-d64a06871906',
            ],
            1,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsWrong(): void
    {
        $object = new GroupGetGroupsAdminsInputDto(
            $this->userSession, [
                'a09ab66e-6413-4d30-8491-1ac491b3f12f',
                '4fd4dcc7-a5dc-4915-af4c-d64a06871906',
            ],
            1,
            -1
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}
