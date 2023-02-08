<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Exception\Fixtures;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class DomainExceptionOutputForTesting extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static(
            $message,
            [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            RESPONSE_STATUS::ERROR,
            RESPONSE_STATUS_HTTP::NO_CONTENT
        );
    }
}
