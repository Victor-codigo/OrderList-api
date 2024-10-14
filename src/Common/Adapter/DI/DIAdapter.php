<?php

declare(strict_types=1);

namespace Common\Adapter\DI;

use Common\Adapter\DI\Exception\RouteInvalidParameterException;
use Common\Adapter\DI\Exception\RouteNotFoundException;
use Common\Adapter\DI\Exception\RouteParametersMissingException;
use Common\Domain\Ports\DI\DIInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException as SymfonyRouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DIAdapter implements DIInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $DI,
        private string $appProtocolAndDomain,
    ) {
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            RouterInterface::class,
            RequestStack::class,
        ];
    }

    /**
     * @param string[] $params
     *
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    #[\Override]
    public function getUrlRouteAbsolute(string $route, array $params): string
    {
        return $this->generateUrl($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param string[] $params
     *
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    #[\Override]
    public function getUrlRouteRelative(string $route, array $params): string
    {
        return $this->generateUrl($route, $params, UrlGeneratorInterface::RELATIVE_PATH);
    }

    /**
     * @param string[] $params
     *
     * @throws RouteNotFoundException
     * @throws RouteParametersMissingException
     * @throws RouteInvalidParameterException
     */
    #[\Override]
    public function getUrlRouteAbsoluteDomain(string $route, array $params): string
    {
        $url = $this->generateUrl($route, $params, UrlGeneratorInterface::ABSOLUTE_PATH);

        return $this->appProtocolAndDomain.$url;
    }

    /**
     * @param string[] $params
     *
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

    private function getRequest(): Request
    {
        return $this->DI->get(RequestStack::class)->getCurrentRequest();
    }

    #[\Override]
    public function getLocale(): string
    {
        return $this->getRequest()->getLocale();
    }
}
