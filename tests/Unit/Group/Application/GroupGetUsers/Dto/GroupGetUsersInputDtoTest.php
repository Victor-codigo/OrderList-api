<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetUsers\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersInputDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;

class GroupGetUsersInputDtoTest extends TestCase
{
    private const string GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /**
     * @return iterable<array<int, GroupGetUsersInputDto|array<string, VALIDATION_ERRORS[]>>>
     */
    public static function inputDataProvider(): iterable
    {
        /** @var Stub&UserShared $userSession */
        $userSession = self::createStub(UserShared::class);

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                FILTER_SECTION::GROUP_USERS->value,
                FILTER_STRING_COMPARISON::EQUALS->value,
                'user name',
                true
            ),
            [],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                FILTER_SECTION::GROUP_USERS->value,
                FILTER_STRING_COMPARISON::STARTS_WITH->value,
                'user name',
                false
            ),
            [],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                FILTER_SECTION::GROUP_USERS->value,
                FILTER_STRING_COMPARISON::ENDS_WITH->value,
                'user name',
                false
            ),
            [],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                FILTER_SECTION::GROUP_USERS->value,
                FILTER_STRING_COMPARISON::CONTAINS->value,
                'user name',
                false
            ),
            [],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                null,
                null,
                null,
                true
            ),
            [],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                null,
                1,
                5,
                null,
                null,
                null,
                true
            ),
            ['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                'wrong id',
                1,
                5,
                null,
                null,
                null,
                true
            ),
            ['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                null,
                5,
                null,
                null,
                null,
                true
            ),
            ['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                -1,
                5,
                null,
                null,
                null,
                true
            ),
            ['page' => [VALIDATION_ERRORS::GREATER_THAN]],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                null,
                null,
                null,
                null,
                true
            ),
            ['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                -1,
                null,
                null,
                null,
                true
            ),
            ['page_items' => [VALIDATION_ERRORS::GREATER_THAN]],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                FILTER_SECTION::GROUP_USERS->value,
                null,
                null,
                true
            ),
            [
                'filter_section_and_text_not_empty' => [VALIDATION_ERRORS::NOT_NULL],
                'section_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            ],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                FILTER_SECTION::GROUP_USERS->value,
                FILTER_STRING_COMPARISON::CONTAINS->value,
                null,
                true
            ),
            [
                'section_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
                'text_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            ],
        ];

        yield [
            new GroupGetUsersInputDto(
                $userSession,
                self::GROUP_ID,
                1,
                5,
                null,
                FILTER_STRING_COMPARISON::CONTAINS->value,
                null,
                true
            ),
            [
                'filter_section_and_text_not_empty' => [VALIDATION_ERRORS::NOT_NULL],
                'text_filter_value' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL],
            ],
        ];
    }

    /**
     * @param array<string, VALIDATION_ERRORS[]> $errors
     */
    #[DataProvider('inputDataProvider')]
    #[Test]
    public function itShouldValidateInput(GroupGetUsersInputDto $object, array $errors): void
    {
        $return = $object->validate($this->validator);

        $this->assertEquals($errors, $return);
    }
}
