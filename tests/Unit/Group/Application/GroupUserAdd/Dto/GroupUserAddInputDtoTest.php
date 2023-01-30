<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserAdd\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserAdd\Dto\GroupUserAddInputDto;
use Group\Domain\Model\GROUP_ROLES;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class GroupUserAddInputDtoTest extends TestCase
{
    private const GROUP_ID = 'acfbced0-26aa-4575-94ba-aca21aa0ef7d';
    private const USERS_TO_ADD_ID = [
        'e01b2650-1fc9-4c18-87f5-747856c03174',
        'e9097370-6640-4ce3-b884-3356503a3abb',
        'aa4db475-c6d5-4184-b675-f97c3a4e6435',
    ];

    private ValidationInterface $validator;
    private User $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userSession = User::fromPrimitives(
            'f34a442c-b3d9-4ba9-b958-d406e3e94415',
            'user@email.com',
            'password',
            'UserName',
            [USER_ROLES::USER]
        );
    }

    /** @test */
    public function itShouldValidateAdminIsTrue(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::USERS_TO_ADD_ID,
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->usersId);
        $this->assertEquals(GROUP_ROLES::ADMIN, $object->rol->getValue());
    }

    /** @test */
    public function itShouldValidateAdminIsFalse(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::USERS_TO_ADD_ID,
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->usersId);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
    }

    /** @test */
    public function itShouldValidateAdminIsNull(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::USERS_TO_ADD_ID,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->usersId);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
    }

    /** @test */
    public function itSouldFailGroupIdIsNull(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            null,
            self::USERS_TO_ADD_ID,
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->usersId);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itSouldFailGroupIdIsNotValid(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            'not valid id',
            self::USERS_TO_ADD_ID,
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->usersId);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itSouldFailUsersIdIsNull(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->usersId);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['users' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itSouldFailNotAllUsersIdAreValid(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            array_merge(self::USERS_TO_ADD_ID, ['not a valid id']),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->usersId);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['users' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
