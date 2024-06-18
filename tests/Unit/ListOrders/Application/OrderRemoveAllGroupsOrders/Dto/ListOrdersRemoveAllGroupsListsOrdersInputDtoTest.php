<?php

declare(strict_types=1);

namespace Test\Unit\ListOrders\Application\OrderRemoveAllGroupsOrders\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrderRemoveAllGroupsListsOrders\Dto\ListOrderRemoveAllGroupsListsOrdersInputDto;
use PHPUnit\Framework\TestCase;

class ListOrdersRemoveAllGroupsListsOrdersInputDtoTest extends TestCase
{
    private const SYSTEM_KEY = 'systemKeyDev';
    private const USER_ID = 'd888b345-2666-4bec-9e5c-daf68c1c68f4';

    private ValidationInterface $validator;
    private UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    private function getGroupsIdToRemove(): array
    {
        return [
            '1fc3cf4c-52f5-496b-81dd-f4aecaf0d166',
            'f84a2e9a-9e27-42b5-8c61-30fbc577c9e7',
            '0b4d354e-5294-467c-8f24-58b36d88a5ad',
        ];
    }

    private function getGroupsIdToChangeUserId(): array
    {
        return [
            '990eb0b3-2904-4f00-aa79-46870c3dbf4b',
            '66c2330e-3d7e-451e-a4fa-6533a2befa96',
            '196c2e3f-18d3-4ab8-ae19-7666d253dc74',
        ];
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdToChangeUserId(),
            self::USER_ID,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateGroupsIdToChangeUserIdIsEmptyAndUserIdIsNull(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            $this->getGroupsIdToRemove(),
            null,
            null,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateGroupsIdToRemoveIsNull(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            null,
            $this->getGroupsIdToChangeUserId(),
            self::USER_ID,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupsIdToRemoveWrong(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            array_merge($this->getGroupsIdToRemove(), ['wrong id']),
            $this->getGroupsIdToChangeUserId(),
            self::USER_ID,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id_remove' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailGroupsIdToChangeUserIdWrong(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            $this->getGroupsIdToRemove(),
            array_merge($this->getGroupsIdToChangeUserId(), ['wrong id']),
            self::USER_ID,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id_change_user_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    /** @test */
    public function itShouldFailGroupsIdToChangeUserIdUserIdIsNull(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdToChangeUserId(),
            null,
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['user_id_set' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupsIdToChangeUserIdUserIdIsWrong(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdToChangeUserId(),
            'wrong id',
            self::SYSTEM_KEY
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['user_id_set' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailSystemKeyIsNull(): void
    {
        $object = new ListOrderRemoveAllGroupsListsOrdersInputDto(
            $this->userSession,
            $this->getGroupsIdToRemove(),
            $this->getGroupsIdToChangeUserId(),
            self::USER_ID,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['system_key' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }
}
