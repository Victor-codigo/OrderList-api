<?php

declare(strict_types=1);

namespace Common\Adapter\ModuleComumication;

use Common\Adapter\ModuleComumication\Exception\ModuleComunicationException;
use Common\Domain\Config\AppConfig;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\HttpClient\Exception\Error500Exception;
use Common\Domain\HttpClient\Exception\NetworkException;
use Common\Domain\ModuleComumication\ModuleComunicationConfigDto;
use Common\Domain\Ports\DI\DIInterface;
use Common\Domain\Ports\HttpClient\HttpClientInterface;
use Common\Domain\Ports\ModuleComunication\ModuleComumunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ModuleComunication implements ModuleComumunicationInterface
{
    private const API_REQUEST_TOKEN_EXPIRATION_TIME = AppConfig::API_TOKEN_RESQUEST_EXPIRE_TIME;
    private const DEV_QUERY_STRING = '?XDEBUG_SESSION=VSCODE&env=test';

    public function __construct(
        private HttpClientInterface $httpClient,
        private DIInterface $DI,
        private JWTTokenManagerInterface $jwtManager,
        private Security $security,
        private string $appEnv
    ) {
    }

    /**
     * @throws Error400Exception
     * @throws ModuleComunicationException
     * @throws ValueError
     */
    public function __invoke(ModuleComunicationConfigDto $routeConfig): ResponseDto
    {
        $respnseContent = $this->request($routeConfig);

        return $this->createResponseDto($respnseContent);
    }

    /**
     * @throws Error400Exception
     * @throws ModuleComunicationException
     */
    private function request(ModuleComunicationConfigDto $routeConfig): array
    {
        try {
            $response = $this->httpClient->request(
                $routeConfig->method,
                $this->getRequestUrl($routeConfig->route, $routeConfig->parameters),
                $this->getOptions($routeConfig->contentType, $routeConfig->parameters, $routeConfig->authentication)
            );

            $responseContent = $response->getContent();
        } catch (Error400Exception|Error500Exception $e) {
            $responseContent = $response->getContent(false);
        } catch (NetworkException $e) {
            throw ModuleComunicationException::fromComunicationError('Error network', $e);
        }

        if ('' === $responseContent) {
            return (new ResponseDto(hasContent: false))->toArray();
        }

        try {
            return json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ModuleComunicationException::fromComunicationError('Error json decode', $e);
        }
    }

    /**
     * @throws \ValueError
     */
    private function createResponseDto(array $responseContent): ResponseDto
    {
        return new ResponseDto(
            $responseContent['data'],
            $responseContent['errors'],
            $responseContent['message'],
            RESPONSE_STATUS::from($responseContent['status']),
            $responseContent['hasContent']
        );
    }

    private function getConfiguration(): array
    {
        return [
            'proxy' => 'http://proxy:80',
            'verify_peer' => false,
            'verify_host' => false,
        ];
    }

    private function getRequestUrl(string $route, array $parameters): string
    {
        $sessionDebug = '';
        if ('dev' == $this->appEnv || 'test' == $this->appEnv) {
            $sessionDebug = static::DEV_QUERY_STRING;
        }

        return $this->DI->getUrlRouteAbsoluteDomain($route, $parameters).$sessionDebug;
    }

    private function json(array $data = null, string $tokenSession = null): array
    {
        $json = $this->getConfiguration();

        null === $data ?: $json['json'] = $data;
        null === $tokenSession ?: $json['auth_bearer'] = $tokenSession;

        return $json;
    }

    private function createTokenSession(UserInterface $userSession): string
    {
        return $this->jwtManager->createFromPayload($userSession, [
            'exp' => time() + static::API_REQUEST_TOKEN_EXPIRATION_TIME,
        ]);
    }

    private function getOptions(string $contentType, array $parameters, bool $authentication): array
    {
        $tokenSession = null;
        if ($authentication) {
            $tokenSession = $this->createTokenSession($this->security->getUser());
        }

        return match ($contentType) {
            'application/json' => $this->json($parameters, $tokenSession)
        };
    }
}
