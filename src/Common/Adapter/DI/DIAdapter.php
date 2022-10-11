<?php

declare(strict_types=1);

namespace Common\Adapter\DI;

use Common\Adapter\DI\Exception\RouteInvalidParameterException;
use Common\Adapter\DI\Exception\RouteNotFoundException;
use Common\Adapter\DI\Exception\RouteParametersMissingException;
use Common\Domain\Ports\DI\DIInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException as SymfonyRouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DIAdapter implements DIInterface, ServiceSubscriberInterface
{
    protected ContainerInterface $DI;

    public static function getSubscribedServices(): array
    {
        return [
            RouterInterface::class,
        ];
    }

    public function __construct(ContainerInterface $DI)
    {
        $this->DI = $DI;
    }

    /**
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    public function getUrlRouteAbsolute(string $route, array $params): string
    {
        return $this->generateUrl($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    public function getUrlRouteRelative(string $route, array $params): string
    {
        return $this->generateUrl($route, $params, UrlGeneratorInterface::RELATIVE_PATH);
    }

    /**
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    private function generateUrl(string $route, array $params, int $type): string
    {
        try {
            return $this->getRouter()->generate($route, $params, $type);
        } catch (SymfonyRouteNotFoundException $e) {
            throw RouteNotFoundException::fromMessage($e->getMessage(), $e->getCode());
        } catch (MissingMandatoryParametersException $e) {
            throw RouteParametersMissingException::fromMessage($e->getMessage(), $e->getCode());
        } catch (InvalidParameterException $e) {
            throw RouteInvalidParameterException::fromMessage($e->getMessage(), $e->getCode());
        }
    }

    private function getRouter(): Router
    {
        return $this->DI->get(RouterInterface::class);
    }
}
