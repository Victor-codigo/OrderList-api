<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserRemove\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\User\USER_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveInputDto;
use PHPUnit\Framework\TestCase;

class GroupUserRemoveInputDtoTest extends TestCase
{
    private const string GROUP_ID = '1dd8f1a0-1ddf-4363-81ce-8ac4e28a410b';
    private const array USERS_TO_REMOVE_ID = [
        'a69386cc-1662-41b3-a39a-55216c115d72',
        'ace4bd94-4771-4a1f-970d-1f33887f3a51',
        '38ce9a0d-f9db-4cdd-ab5e-5c6fd0367a7a',
    ];

    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function getUserSession(): UserShared
    {
        return UserShared::fromPrimitives('', '', '', [USER_ROLES::USER], null, new \DateTime());
    }

    #[Test]
    public function itShouldValidateGroupUserRemoveValidation(): void
    {
        $userSession = $this->getUserSession();
        $object = new GroupUserRemoveInputDto($userSession, self::GROUP_ID, self::USERS_TO_REMOVE_ID);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailGroupIdIsNull(): void
    {
        $userSession = $this->getUserSession();
        $object = new GroupUserRemoveInputDto($userSession, null, self::USERS_TO_REMOVE_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupIdNotValid(): void
    {
        $userSession = $this->getUserSession();
        $object = new GroupUserRemoveInputDto($userSession, 'not valid id', self::USERS_TO_REMOVE_ID);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailUsersIdIsNull(): void
    {
        $userSession = $this->getUserSession();
        $object = new GroupUserRemoveInputDto($userSession, self::GROUP_ID, null);

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailUsersIdNotValid(): void
    {
        $userSession = $this->getUserSession();
        $usersToRemove = array_merge(self::USERS_TO_REMOVE_ID, ['user id not valid']);
        $object = new GroupUserRemoveInputDto($userSession, self::GROUP_ID, $usersToRemove);

        $return = $object->validate($this->validator);

        $this->assertEquals(['users_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
