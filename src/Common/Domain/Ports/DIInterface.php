<?php

declare(strict_types=1);

namespace Common\Domain\Ports;

interface DIInterface
{
    public function getUrlRouteAbsolute(string $route, array $params): string;

    public function getUrlRouteRelative(string $route, array $params): string;
}
