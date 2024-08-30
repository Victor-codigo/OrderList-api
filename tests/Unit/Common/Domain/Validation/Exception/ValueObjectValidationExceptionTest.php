<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Validation\Exception;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use PHPUnit\Framework\TestCase;

class ValueObjectValidationExceptionTest extends TestCase
{
    #[Test]
    public function isShouldCreateTheExceptionFromAMessageAndAListOfErrors(): void
    {
        $message = 'message';
        $errors = [
            'key 1' => [VALIDATION_ERRORS::ALPHANUMERIC, VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
            'key 2' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH, VALIDATION_ERRORS::CHOICE_TOO_FEW],
            'key 3' => [VALIDATION_ERRORS::DATE_INVALID_FORMAT, VALIDATION_ERRORS::DATE_INVALID],
        ];
        $errorsExpected = [
            'key 1' => [mb_strtolower(VALIDATION_ERRORS::ALPHANUMERIC->name), mb_strtolower(VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE->name)],
            'key 2' => [mb_strtolower(VALIDATION_ERRORS::CHOICE_NOT_SUCH->name), mb_strtolower(VALIDATION_ERRORS::CHOICE_TOO_FEW->name)],
            'key 3' => [mb_strtolower(VALIDATION_ERRORS::DATE_INVALID_FORMAT->name), mb_strtolower(VALIDATION_ERRORS::DATE_INVALID->name)],
        ];

        $return = ValueObjectValidationException::fromArray($message, $errors);

        $this->assertEquals(RESPONSE_STATUS_HTTP::BAD_REQUEST, $return->httpStatus);
        $this->assertEquals($errorsExpected, $return->getErrors());
    }

    #[Test]
    public function isShouldCreateTheExceptionFromAMessageAndAListOfGroupsOfErrors(): void
    {
        $message = 'message';
        $errors = [
            'key 1' => [
                [VALIDATION_ERRORS::ALPHANUMERIC, VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
                [VALIDATION_ERRORS::ALPHANUMERIC, VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
            ],
            'key 2' => [
                [VALIDATION_ERRORS::CHOICE_NOT_SUCH, VALIDATION_ERRORS::CHOICE_TOO_FEW],
                [VALIDATION_ERRORS::CHOICE_NOT_SUCH, VALIDATION_ERRORS::CHOICE_TOO_FEW],
            ],
            'key 3' => [
                [VALIDATION_ERRORS::DATE_INVALID_FORMAT, VALIDATION_ERRORS::DATE_INVALID],
                [VALIDATION_ERRORS::DATE_INVALID_FORMAT, VALIDATION_ERRORS::DATE_INVALID],
            ],
        ];
        $errorsExpected = [
            'key 1' => [
                [mb_strtolower(VALIDATION_ERRORS::ALPHANUMERIC->name), mb_strtolower(VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE->name)],
                [mb_strtolower(VALIDATION_ERRORS::ALPHANUMERIC->name), mb_strtolower(VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE->name)],
            ],
            'key 2' => [
                [mb_strtolower(VALIDATION_ERRORS::CHOICE_NOT_SUCH->name), mb_strtolower(VALIDATION_ERRORS::CHOICE_TOO_FEW->name)],
                [mb_strtolower(VALIDATION_ERRORS::CHOICE_NOT_SUCH->name), mb_strtolower(VALIDATION_ERRORS::CHOICE_TOO_FEW->name)],
            ],
            'key 3' => [
                [mb_strtolower(VALIDATION_ERRORS::DATE_INVALID_FORMAT->name), mb_strtolower(VALIDATION_ERRORS::DATE_INVALID->name)],
                [mb_strtolower(VALIDATION_ERRORS::DATE_INVALID_FORMAT->name), mb_strtolower(VALIDATION_ERRORS::DATE_INVALID->name)],
            ],
        ];

        $return = ValueObjectValidationException::fromArray($message, $errors);

        $this->assertEquals(RESPONSE_STATUS_HTTP::BAD_REQUEST, $return->httpStatus);
        $this->assertEquals($errorsExpected, $return->getErrors());
    }
}
