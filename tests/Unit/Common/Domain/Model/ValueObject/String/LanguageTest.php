<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    private ValidationInterface $validator;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createLanguage(?string $language): Language
    {
        return new Language($language);
    }

    #[Test]
    public function languageOk(): void
    {
        $token = $this->createLanguage('en');
        $return = $this->validator->validateValueObject($token);

        $this->assertEmpty($return);
    }

    #[Test]
    public function languageNotBlank(): void
    {
        $language = $this->createLanguage('');
        $return = $this->validator->validateValueObject($language);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }

    #[Test]
    public function languageNotNull(): void
    {
        $language = $this->createLanguage(null);
        $return = $this->validator->validateValueObject($language);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    #[Test]
    public function languageNoValid(): void
    {
        $language = $this->createLanguage('eng');
        $return = $this->validator->validateValueObject($language);

        $this->assertEquals([VALIDATION_ERRORS::LANGUAGE, VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }

    #[Test]
    public function languageNotEnglishOrSpanishLanguage(): void
    {
        $language = $this->createLanguage('fr');
        $return = $this->validator->validateValueObject($language);

        $this->assertEquals([VALIDATION_ERRORS::CHOICE_NOT_SUCH], $return);
    }
}
