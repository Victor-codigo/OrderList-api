<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserRolChangeInputDto\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\User\USER_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserRoleChange\Dto\GroupUserRoleChangeInputDto;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupUserRolChangeInputDtoTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const USERS_ID = [
        self::GROUP_USER_ADMIN_ID,
        '20354d7a-e4fe-47af-8ff6-187bca92f3f9',
        'caa8b54a-eb5e-4134-8ae2-a3946a428ec7',
        'bd2cbad1-6ccf-48e3-bb92-bc9961bc011e',
     ];

    private ValidationInterface $validator;
    private User $usersSesion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->usersSesion = User::fromPrimitives(
            self::GROUP_USER_ADMIN_ID,
            'email@host.com',
            'password',
            'name',
            [USER_ROLES::USER]
        );
    }

    /** @test */
    public function itShouldValidateTheInput(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            self::GROUP_ID,
            self::USERS_ID,
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            null,
            self::USERS_ID,
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            'not valid id',
            self::USERS_ID,
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailUsersIdIsNull(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            self::GROUP_ID,
            null,
            true
        );

        $return = $object->validate($this->validator);
        $this->assertEquals(['users' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    /** @test */
    public function itShouldFailUsersIdNotValid(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            self::GROUP_ID,
            ['not valid id', 'not valid id'],
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['users' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailAdminIsNull(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            self::GROUP_ID,
            self::USERS_ID,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
    }

    /** @test */
    public function itShouldFailAdminIsFalse(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            self::GROUP_ID,
            self::USERS_ID,
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
    }

    /** @test */
    public function itShouldFailAdminIsTrue(): void
    {
        $object = new GroupUserRoleChangeInputDto(
            $this->usersSesion,
            self::GROUP_ID,
            self::USERS_ID,
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertEquals(GROUP_ROLES::ADMIN, $object->rol->getValue());
    }
}
