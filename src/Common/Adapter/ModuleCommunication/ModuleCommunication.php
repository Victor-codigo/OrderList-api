<?php

declare(strict_types=1);

namespace Common\Adapter\ModuleCommunication;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Domain\Config\AppConfig;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\HttpClient\Exception\Error500Exception;
use Common\Domain\HttpClient\Exception\NetworkException;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\DI\DIInterface;
use Common\Domain\Ports\HttpClient\HttpClientInterface;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Security\Core\User\UserInterface;

class ModuleCommunication implements ModuleCommunicationInterface
{
    private const API_REQUEST_TOKEN_EXPIRATION_TIME = AppConfig::API_TOKEN_REQUEST_EXPIRE_TIME;
    private const DEV_QUERY_STRING = 'XDEBUG_SESSION=VSCODE';
    private const TEST_QUERY_STRING = 'env=test';

    public function __construct(
        private HttpClientInterface $httpClient,
        private DIInterface $DI,
        private JWTTokenManagerInterface $jwtManager,
        private Security $security,
        private string $appEnv
    ) {
    }

    /**
     * @throws ModuleCommunicationException
     * @throws ValueError
     */
    public function __invoke(ModuleCommunicationConfigDto $routeConfig): ResponseDto
    {
        try {
            $response = $this->httpClient->request(
                $routeConfig->method,
                $this->getRequestUrl($routeConfig->route, array_merge($routeConfig->attributes, $routeConfig->query)),
                $this->getOptions($routeConfig->contentType, $routeConfig->content, $routeConfig->files, $routeConfig->authentication)
            );

            $responseContent = $response->getContent();
            $responseHeaders = $response->getHeaders();
        } catch (Error400Exception|Error500Exception $e) {
            $responseContent = $response->getContent(false);
            $responseHeaders = $response->getHeaders(false);
        } catch (NetworkException $e) {
            throw ModuleCommunicationException::fromCommunicationError('Error network', $e);
        }

        if ('' === $responseContent) {
            return $this->createResponseDto([], $responseHeaders);
        }

        try {
            return $this->createResponseDto(
                json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR),
                $responseHeaders
            );
        } catch (\JsonException $e) {
            throw ModuleCommunicationException::fromCommunicationError('Error json decode', $e);
        }
    }

    /**
     * @throws \ValueError
     */
    private function createResponseDto(array $responseContent, array $responseHeaders): ResponseDto
    {
        return new ResponseDto(
            isset($responseContent['data']) ? $responseContent['data'] : [],
            isset($responseContent['errors']) ? $responseContent['errors'] : [],
            isset($responseContent['message']) ? $responseContent['message'] : '',
            isset($responseContent['status']) ? RESPONSE_STATUS::from($responseContent['status']) : RESPONSE_STATUS::OK,
            empty($responseContent) ? false : true,
            $responseHeaders
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

    private function getRequestUrl(string $route, array $attributes): string
    {
        $sessionDebug = '';

        if ('dev' === $this->appEnv || 'test' === $this->appEnv) {
            $sessionDebug = '?'.static::DEV_QUERY_STRING;
        }

        if ('test' == $this->appEnv) {
            $sessionDebug .= '&'.static::TEST_QUERY_STRING;
        }

        return $this->DI->getUrlRouteAbsoluteDomain($route, $attributes).$sessionDebug;
    }

    private function json(array $data = null, string $tokenSession = null): array
    {
        $json = $this->getConfiguration();

        null === $data ?: $json['json'] = $data;
        null === $tokenSession ?: $json['auth_bearer'] = $tokenSession;

        return $json;
    }

    private function form(array $data = null, array $files = null, string $tokenSession = null): array
    {
        $form = $this->getConfiguration();

        null === $tokenSession ?: $form['auth_bearer'] = $tokenSession;

        null === $data ?: $formFields = $data;
        null === $files ?: $formFields = array_merge($formFields, $files);
        $formData = new FormDataPart($formFields);
        $form['body'] = $formData->bodyToIterable();
        $form['headers'] = $formData->getPreparedHeaders()->toArray();

        return $form;
    }

    private function createTokenSession(UserInterface $userSession): string
    {
        return $this->jwtManager->createFromPayload($userSession, [
            'exp' => time() + static::API_REQUEST_TOKEN_EXPIRATION_TIME,
        ]);
    }

    private function getOptions(string $contentType, array $content = [], array $files = [], bool $authentication = false): array
    {
        $tokenSession = null;
        if ($authentication) {
            $tokenSession = $this->createTokenSession($this->security->getUser());
        }

        return match ($contentType) {
            'application/json' => $this->json($content, $tokenSession),
            'multipart/form-data' => $this->form($content, $files, $tokenSession)
        };
    }
}
