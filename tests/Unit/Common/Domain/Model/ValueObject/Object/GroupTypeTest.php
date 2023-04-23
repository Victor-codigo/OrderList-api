<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Group\Domain\Model\GROUP_TYPE;
use PHPUnit\Framework\TestCase;

class GroupTypeTest extends TestCase
{
    private ValidationChain $validation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    /** @test */
    public function itShouldValidateTheGroupType(): void
    {
        $object = new GroupType(GROUP_TYPE::GROUP);
        $return = $this->validation->validateValueObject($object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailIsNull(): void
    {
        $object = new GroupType(null);
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_NULL], $return);
    }

    /** @test */
    public function itShouldFailIsBank(): void
    {
        $object = new GroupType(new \stdClass());
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }
}
