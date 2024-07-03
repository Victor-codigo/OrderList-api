<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Constraints\Alphanumeric;

use Symfony\Component\Validator\Constraints\Regex;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Alphanumeric extends Regex
{
    public const ALPHANUMERIC_FAILED_ERROR = '93b433da-0054-405c-ba5c-3bf1a26f2254';

    protected const ERROR_NAMES = [
        self::ALPHANUMERIC_FAILED_ERROR => 'ALPHANUMERIC_FAILED_ERROR',
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
            '/^[A-Za-zÀ-ÿ0-9_]+$/i',
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
