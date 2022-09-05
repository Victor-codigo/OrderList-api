<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\EMAIL_TYPES;
use Common\Domain\Validation\TYPES;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Unique;

class ValidationGeneral extends ValidationConstraintBase
{
    public function notBlank(): ValidationConstraint
    {
        return $this->createConstraint(
            new NotBlank(),
            [NotBlank::IS_BLANK_ERROR => VALIDATION_ERRORS::NOT_BLANK]
        );
    }

    public function notNull(): ValidationConstraint
    {
        return $this->createConstraint(
            new NotNull(),
            [NotNull::IS_NULL_ERROR => VALIDATION_ERRORS::NOT_NULL]
        );
    }

    public function email(EMAIL_TYPES $mode): ValidationConstraint
    {
        $emailModes = [
            EMAIL_TYPES::HTML5->value => Email::VALIDATION_MODE_HTML5,
            EMAIL_TYPES::LOOSE->value => Email::VALIDATION_MODE_LOOSE,
            EMAIL_TYPES::STRICT->value => Email::VALIDATION_MODE_STRICT,
        ];

        return $this->createConstraint(
            new Email(null, null, $emailModes[$mode->value]),
            [Email::INVALID_FORMAT_ERROR => VALIDATION_ERRORS::EMAIL]
        );
    }

    public function type(TYPES $type): ValidationConstraint
    {
        return $this->createConstraint(
            new Type($type->value),
            [Type::INVALID_TYPE_ERROR => VALIDATION_ERRORS::TYPE]
        );
    }

    public function unique(): ValidationConstraint
    {
        return $this->createConstraint(
            new Unique(),
            [Unique::IS_NOT_UNIQUE => VALIDATION_ERRORS::UNIQUE],
        );
    }
}
