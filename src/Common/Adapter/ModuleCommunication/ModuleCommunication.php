<?php

declare(strict_types=1);

namespace Common\Adapter\ModuleCommunication;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationErrorResponseException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationTokenNotFoundInRequestException;
use Common\Domain\Config\AppConfig;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\HttpClient\Exception\Error500Exception;
use Common\Domain\HttpClient\Exception\NetworkException;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDtoPaginatorInterface;
use Common\Domain\Ports\DI\DIInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Ports\HttpClient\HttpClientInterface;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class ModuleCommunication implements ModuleCommunicationInterface
{
    private const COMMUNICATION_CONFIG = [
        'proxy' => AppConfig::MODULE_COMMUNICATION_REQUEST_PROXY,
        'verify_peer' => AppConfig::MODULE_COMMUNICATION_REQUEST_HTTPS['verify_peer'],
        'verify_host' => AppConfig::MODULE_COMMUNICATION_REQUEST_HTTPS['verify_host'],
    ];

    private const DEV_QUERY_STRING = 'XDEBUG_SESSION=VSCODE';
    private const TEST_QUERY_STRING = 'env=test';

    public function __construct(
        private HttpClientInterface $httpClient,
        private DIInterface $DI,
        private TokenExtractorInterface $jwtTokenExtractor,
        private RequestStack $request,
        private string $appEnv
    ) {
    }

    /**
     * @throws ModuleCommunicationException
     * @throws ValueError
     * @throws ModuleCommunicationTokenNotFoundInRequestException
     */
    public function __invoke(ModuleCommunicationConfigDtoPaginatorInterface $routeConfig): ResponseDto
    {
        try {
            $response = $this->httpClient->request(
                $routeConfig->method,
                $this->getRequestUrl($routeConfig->route, array_merge($routeConfig->attributes, $routeConfig->query)),
                $this->getOptions($routeConfig->authentication, $routeConfig->contentType, $routeConfig->content, $routeConfig->files, $routeConfig->cookies, $routeConfig->headers)
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
     * @throws ModuleCommunicationException
     * @throws ValueError
     * @throws ModuleCommunicationTokenNotFoundInRequestException
     * @throws ModuleCommunicationErrorResponseException
     * @throws InvalidArgumentException
     */
    public function getPagesRangeEndpoint(ModuleCommunicationConfigDtoPaginatorInterface $routeConfig, int $pageIni, ?int $pageEnd): \Generator
    {
        if ($pageIni < 1) {
            throw new InvalidArgumentException('PageIni cannot be less than 1');
        }

        $hasPageEnd = null === $pageEnd ? false : true;
        $pageEnd ??= $pageIni;
        $pagesTotal = $pageIni;
        $firstCall = true;
        while ($pageIni <= $pagesTotal && $pageIni <= $pageEnd) {
            $routeConfigActual = $routeConfig->cloneWithPage($pageIni);
            $response = $this->__invoke($routeConfigActual);

            if (RESPONSE_STATUS::OK !== $response->getStatus()
            || !empty($response->getErrors())) {
                throw ModuleCommunicationErrorResponseException::fromResponseError($response->getErrors());
            }

            yield $response->data;

            if (!$response->hasContent()) {
                return;
            }

            if ($firstCall) {
                $pagesTotal = $this->getArrayValueByPath($response->data, $routeConfig->getResponsePagesTotalPath());
                $pageEnd = $hasPageEnd ? $pageEnd : $pagesTotal;
                $firstCall = false;
            }

            ++$pageIni;
        }
    }

    /**
     * @throws ModuleCommunicationException
     * @throws ValueError
     * @throws ModuleCommunicationTokenNotFoundInRequestException
     * @throws ModuleCommunicationErrorResponseException
     * @throws InvalidArgumentException
     */
    public function getAllPagesOfEndpoint(ModuleCommunicationConfigDtoPaginatorInterface $routeConfig): \Generator
    {
        return $this->getPagesRangeEndpoint($routeConfig, 1, null);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getArrayValueByPath(array $array, string $path): string|int|float|array
    {
        $pathInArray = explode('.', $path);

        $arrayPointer = &$array;
        foreach ($pathInArray as $index) {
            if (!array_key_exists($index, $arrayPointer)) {
                throw new InvalidArgumentException('Path not found in array');
            }

            $arrayPointer = &$arrayPointer[$index];
        }

        return $arrayPointer;
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

    private function getRequestUrl(string $route, array $attributes): string
    {
        $url = $this->DI->getUrlRouteAbsoluteDomain($route, $attributes);

        if ('dev' !== $this->appEnv && 'test' !== $this->appEnv) {
            return $url;
        }

        $sessionDebug = static::DEV_QUERY_STRING;

        if ('test' === $this->appEnv) {
            $sessionDebug .= '&'.static::TEST_QUERY_STRING;
        }

        if (str_contains($url, '?')) {
            return str_replace('?', '?'.$sessionDebug.'&', $url);
        }

        return "{$url}?{$sessionDebug}";
    }

    /**
     * @param Cookie[] $cookies
     * @param string[] $headers
     */
    private function json(?string $tokenSession = null, array $data = [], array $cookies = [], array $headers = []): array
    {
        $json = self::COMMUNICATION_CONFIG;

        empty($data) ?: $json['json'] = $data;
        null === $tokenSession ?: $json['auth_bearer'] = $tokenSession;
        empty($headers) && empty($cookies) ?: $json['headers'] = array_merge($headers, $cookies);

        return $json;
    }

    /**
     * @param UploadedFileInterface[] $files
     * @param Cookie[]                $cookies
     * @param string[]                $headers
     */
    private function form(?string $tokenSession = null, array $data = [], array $files = [], array $cookies = [], array $headers = []): array
    {
        $form = self::COMMUNICATION_CONFIG;
        $formFields = [];

        null === $tokenSession ?: $form['auth_bearer'] = $tokenSession;
        empty($data) ?: $formFields = $data;
        empty($files) ?: $formFields = array_merge($formFields, $files);

        $formData = new FormDataPart($formFields);
        $form['body'] = $formData->bodyToIterable();
        $form['headers'] = array_merge($headers, $cookies, $formData->getPreparedHeaders()->toArray());

        return $form;
    }

    /**
     * @param UploadedFileInterface[] $files
     * @param Cookie                  $cookies
     *
     * @throws ModuleCommunicationTokenNotFoundInRequestException
     */
    private function getOptions(bool $authentication, string $contentType, array $content = [], array $files = [], array $cookies = [], array $headers = []): array
    {
        $tokenSession = null;
        if ($authentication) {
            $tokenSession = $this->getAuthentication($headers);
        }

        return match ($contentType) {
            'application/json' => $this->json($tokenSession, $content, $cookies, $headers),
            'multipart/form-data' => $this->form($tokenSession, $content, $files, $cookies, $headers)
        };
    }

    /**
     * @param string[] $headers
     *
     * @throws ModuleCommunicationTokenNotFoundInRequestException
     */
    private function getAuthentication(array $headers): string
    {
        $tokenSession = $this->jwtTokenExtractor->extract($this->request->getCurrentRequest());

        if (false !== $tokenSession) {
            return $tokenSession;
        }

        if (array_key_exists('Authorization', $headers)) {
            $tokenSession = $this->getBearerToken($headers['Authorization']);
        }

        if (null === $tokenSession || false === $tokenSession) {
            throw ModuleCommunicationTokenNotFoundInRequestException::fromMessage('Token not found in request');
        }

        return $tokenSession;
    }

    private function getBearerToken(string $token): ?string
    {
        $tokenSession = explode(' ', $token);

        if ('Bearer' !== $tokenSession[0]) {
            return null;
        }

        return $tokenSession[1];
    }
}
