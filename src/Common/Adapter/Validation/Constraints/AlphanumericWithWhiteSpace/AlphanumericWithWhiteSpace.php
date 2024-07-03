<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Constraints\AlphanumericWithWhiteSpace;

use Attribute;
use Symfony\Component\Validator\Constraints\Regex;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AlphanumericWithWhiteSpace extends Regex
{
    public const ALPHANUMERIC_WITH_WHITESPACE_FAILED_ERROR = '583e7e73-a761-444d-9250-a0783f339ad1';

    protected const ERROR_NAMES = [
        self::ALPHANUMERIC_WITH_WHITESPACE_FAILED_ERROR => 'ALPHANUMERIC_WITH_WHITESPACE_FAILED_ERROR',
    ];

    /**
     * @deprecated since Symfony 6.1, use const ERROR_NAMES instead
     */
    protected static $errorNames = self::ERROR_NAMES;

    public function __construct(
        ?string $message = null,
        ?string $htmlPattern = null,
        ?callable $normalizer = null,
        ?array $groups = null,
        mixed $payload = null,
        array $options = []
    ) {
        parent::__construct(
            '/^[A-Za-zÀ-ÿ0-9_\s]+$/i',
            $message,
            $htmlPattern,
            true,
            $normalizer,
            $groups,
            $payload,
            $options
        );
    }
}
