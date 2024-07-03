<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserGetGroups\Dto;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserGetGroups\Dto\GroupUserGetGroupsInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupUserGetGroupsInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            100,
            GROUP_TYPE::GROUP->value,
            FILTER_SECTION::GROUP->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateWithoutFilter(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            100,
            null,
            null,
            null,
            null,
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            null,
            100,
            GROUP_TYPE::USER->value,
            FILTER_SECTION::GROUP->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsLowerThanOne(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            -1,
            100,
            null,
            FILTER_SECTION::GROUP->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            null,
            GROUP_TYPE::GROUP->value,
            FILTER_SECTION::GROUP->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsLowerThanOne(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            -1,
            GROUP_TYPE::USER->value,
            FILTER_SECTION::GROUP->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailFilterSectionIsNull(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            100,
            null,
            null,
            FILTER_STRING_COMPARISON::EQUALS->value,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['filter_section_and_text_not_empty' => [VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailFilterSectionIsWrong(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            100,
            GROUP_TYPE::GROUP->value,
            'wrong section',
            FILTER_STRING_COMPARISON::EQUALS->value,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['section_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailFilterTextIsNull(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            100,
            GROUP_TYPE::USER->value,
            FILTER_SECTION::GROUP->value,
            null,
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['filter_section_and_text_not_empty' => [VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailFilterTextIsWrong(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            100,
            null,
            FILTER_SECTION::GROUP->value,
            'wrong section',
            'group name',
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals(['text_filter_type' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailFilterValueIsNull(): void
    {
        $object = new GroupUserGetGroupsInputDto(
            $this->userSession,
            1,
            100,
            GROUP_TYPE::GROUP->value,
            FILTER_SECTION::GROUP->value,
            FILTER_STRING_COMPARISON::EQUALS->value,
            null,
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEquals([
            'section_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            'text_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
        ],
            $return
        );
    }
}
