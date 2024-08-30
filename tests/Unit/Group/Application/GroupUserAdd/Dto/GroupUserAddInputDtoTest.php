<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupUserAdd\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\User\USER_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserAdd\Dto\GroupUserAddInputDto;
use PHPUnit\Framework\TestCase;

class GroupUserAddInputDtoTest extends TestCase
{
    private const string GROUP_ID = 'acfbced0-26aa-4575-94ba-aca21aa0ef7d';
    private const array USERS_TO_ADD_ID = [
        'e01b2650-1fc9-4c18-87f5-747856c03174',
        'e9097370-6640-4ce3-b884-3356503a3abb',
        'aa4db475-c6d5-4184-b675-f97c3a4e6435',
    ];

    private ValidationInterface $validator;
    private UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userSession = UserShared::fromPrimitives(
            'f34a442c-b3d9-4ba9-b958-d406e3e94415',
            'user@email.com',
            'UserName',
            [USER_ROLES::USER],
            null,
            new \DateTime()
        );
    }

    #[Test]
    public function itShouldValidateAdminIsTrue(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::USERS_TO_ADD_ID,
            'identifier',
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->users);
        $this->assertEquals(GROUP_ROLES::ADMIN, $object->rol->getValue());
    }

    #[Test]
    public function itShouldValidateAdminIsFalse(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::USERS_TO_ADD_ID,
            'identifier',
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->users);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
    }

    #[Test]
    public function itShouldValidateAdminIsNull(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            self::USERS_TO_ADD_ID,
            'identifier',
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->users);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
    }

    #[Test]
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            null,
            self::USERS_TO_ADD_ID,
            'identifier',
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->users);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            'not valid id',
            self::USERS_TO_ADD_ID,
            'identifier',
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->users);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailUsersIdIsNull(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            null,
            'identifier',
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->users);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['users' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailNotAllUsersIdAreValid(): void
    {
        $object = new GroupUserAddInputDto(
            $this->userSession,
            self::GROUP_ID,
            array_merge(self::USERS_TO_ADD_ID, ['not a valid id']),
            'identifier',
            false
        );

        $return = $object->validate($this->validator);

        $this->assertContainsOnlyInstancesOf(Identifier::class, $object->users);
        $this->assertEquals(GROUP_ROLES::USER, $object->rol->getValue());
        $this->assertEquals(['users' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
