<?php

declare(strict_types=1);

namespace Common\Domain\Ports\DI;

interface DIInterface
{
    /**
     * @param string[] $params
     *
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    public function getUrlRouteAbsolute(string $route, array $params): string;

    /**
     * @param string[] $params
     *
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    public function getUrlRouteRelative(string $route, array $params): string;

    /**
     * @param string[] $params
     *
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    public function getUrlRouteAbsoluteDomain(string $route, array $params): string;

    public function getLocale(): string;
}
