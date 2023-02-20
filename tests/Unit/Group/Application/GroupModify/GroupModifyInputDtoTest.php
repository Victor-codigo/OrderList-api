<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupModify;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupModify\Dto\GroupModifyInputDto;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class GroupModifyInputDtoTest extends TestCase
{
    private const GROUP_USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private GroupModifyInputDto $object;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function getUser(): User
    {
        return User::fromPrimitives(self::GROUP_USER_ID, 'email@domain.com', 'password', 'UserName', [USER_ROLES::USER]);
    }

    /** @test */
    public function itShouldValidateTheInput(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDescriptionIsNull(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            null,
            $userNameModify,
            $userDescriptionModify
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            'group id not valid',
            $userNameModify,
            $userDescriptionModify
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $user = $this->getUser();
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            null,
            $userDescriptionModify
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNotValid(): void
    {
        $user = $this->getUser();
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            'not valid name',
            $userDescriptionModify
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionIsTooLong22(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = str_pad('', 501, 'p');
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }
}
