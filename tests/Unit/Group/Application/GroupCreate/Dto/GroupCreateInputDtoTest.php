<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupCreate\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupCreate\Dto\GroupCreateInputDto;
use PHPUnit\Framework\TestCase;

class GroupCreateInputDtoTest extends TestCase
{
    private const GROUP_ID = '452618d5-17fa-4c16-8825-f4f540fca822';

    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createNewGroupCreateInputDto(): GroupCreateInputDto
    {
        return new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'this is a description of the group'
        );
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = $this->createNewGroupCreateInputDto();
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDescriptionIsNull(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            null
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            null,
            'This is a description of the group'
        );
        $return = $object->validate($this->validator);

        $this->assertSame(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNameIsWrong(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'Group Name',
            'This is a description of the group'
        );
        $return = $object->validate($this->validator);

        $this->assertSame(['name' => [VALIDATION_ERRORS::ALPHANUMERIC]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            str_pad('', 501, 'f')
        );
        $return = $object->validate($this->validator);

        $this->assertSame(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }
}
