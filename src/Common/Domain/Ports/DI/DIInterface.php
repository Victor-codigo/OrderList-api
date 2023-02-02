<?php

declare(strict_types=1);

namespace Common\Domain\Ports\DI;

interface DIInterface
{
    /**
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    public function getUrlRouteAbsolute(string $route, array $params): string;

    /**
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    public function getUrlRouteRelative(string $route, array $params): string;

    public function getLocale(): string;
}
