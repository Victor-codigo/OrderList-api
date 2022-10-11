<?php

declare(strict_types=1);

namespace Common\Adapter\DI\Exception;

use Common\Domain\Exception\InvalidArgumentException;

class RouteInvalidParameterException extends InvalidArgumentException implements RouterException
{
}
