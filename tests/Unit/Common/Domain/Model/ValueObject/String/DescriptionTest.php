<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class DescriptionTest extends TestCase
{
    private ValidationInterface $validation;

    private const string VALID_DESCRIPTION = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam consequat nisi vel tempor luctus. Pellentesque sit amet dignissim lorem. Ut vestibulum arcu dui, mattis ullamcorper quam lacinia tincidunt. Suspendisse eu risus eu massa fermentum interdum ut sed magna. Nunc ut tortor nibh. Sed nec bibendum nulla, sit amet fringilla odio. Praesent aliquam euismod faucibus. Nullam eget ipsum ac urna imperdiet molestie. Aliquam maximus, velit suscipit tincidunt condimentum, augue odio consectetur vel.';

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    public function testValidDescription(): void
    {
        $description = ValueObjectFactory::createDescription(self::VALID_DESCRIPTION);
        $return = $this->validation->validateValueObject($description);

        $this->assertEmpty($return);
    }

    public function testValidDescriptionAsNull(): void
    {
        $description = ValueObjectFactory::createDescription(null);
        $return = $this->validation->validateValueObject($description);

        $this->assertEmpty($return);
    }

    public function testValidNameNotTooLong(): void
    {
        $description = ValueObjectFactory::createDescription(self::VALID_DESCRIPTION.'1');
        $return = $this->validation->validateValueObject($description);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return);
    }
}
