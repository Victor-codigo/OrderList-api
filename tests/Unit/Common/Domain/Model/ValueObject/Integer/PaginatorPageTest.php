<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Integer;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class PaginatorPageTest extends TestCase
{
    private ValidationInterface $validation;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    #[Test]
    public function itShouldValidatePageIsOne(): void
    {
        $object = ValueObjectFactory::createPaginatorPage(1);
        $return = $this->validation->validateValueObject($object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailPageIsNull(): void
    {
        $object = ValueObjectFactory::createPaginatorPage(null);
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    #[Test]
    public function itShouldFailPageIsZero(): void
    {
        $object = ValueObjectFactory::createPaginatorPage(0);
        $return = $this->validation->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::GREATER_THAN], $return);
    }
}
