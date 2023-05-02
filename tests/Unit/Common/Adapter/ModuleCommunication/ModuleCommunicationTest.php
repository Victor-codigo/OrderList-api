<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\ModuleCommunication;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\ModuleCommunication\ModuleCommunication;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\HttpClient\Exception\Error500Exception;
use Common\Domain\HttpClient\Exception\NetworkException;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\DI\DIInterface;
use Common\Domain\Ports\HttpClient\HttpClientInterface;
use Common\Domain\Ports\HttpClient\HttpClientResponseInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Test\Unit\Common\Adapter\ModuleCommunication\Fixtures\ModuleCommunicationFactoryTest;

class ModuleCommunicationTest extends TestCase
{
    private const URL = 'http://domain.com/url/to/resource';
    private const JWT_TOKEN = 'asdgasdfbadsfhaetrhadfhbadfhbaerth';
    private const IMAGE_PATH = 'tests/Fixtures/Files/Image.png';

    private ModuleCommunication $object;
    private MockObject|HttpClientInterface $httpClient;
    private MockObject|HttpClientResponseInterface  $httpClientResponse;
    private MockObject|DIInterface $DI;
    private MockObject|TokenExtractorInterface $jwtTokenExtractor;
    private MockObject|RequestStack $requestStack;
    private MockObject|Request $request;
    private string $appEnv;
    private string $responseContentExpected;
    private int $getContentNumCalls;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientResponse = $this->createMock(HttpClientResponseInterface::class);
        $this->DI = $this->createMock(DIInterface::class);
        $this->jwtTokenExtractor = $this->createMock(TokenExtractorInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);
        $this->appEnv = 'prod';
        $this->responseContentExpected = json_encode($this->getResponseDefaultDto());
        $this->getContentNumCalls = 0;

        $this->object = new ModuleCommunication(
            $this->httpClient,
            $this->DI,
            $this->jwtTokenExtractor,
            $this->requestStack,
            $this->appEnv
        );
    }

    private function getResponseDefaultDto(): ResponseDto
    {
        return (new ResponseDto())
            ->setMessage('response message')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData(['key1' => 'value1', 'key2' => 'value2'])
            ->setErrors(['key1' => 'value1', 'key2' => 'value2']);
    }

    private function getResponseDtoFromString(string $responseContent): ResponseDto
    {
        if ('' === $responseContent) {
            return (new ResponseDto(hasContent: false))
                ->setMessage('')
                ->setStatus(RESPONSE_STATUS::OK)
                ->setData([])
                ->setErrors([]);
        }

        $responseDto = json_decode($responseContent, true);

        return (new ResponseDto())
            ->setMessage($responseDto['message'])
            ->setStatus(RESPONSE_STATUS::from($responseDto['status']))
            ->setData($responseDto['data'])
            ->setErrors($responseDto['errors']);
    }

    private function mockRequestMethod(ModuleCommunicationConfigDto $routeConfig, \DomainException $requestException = null): void
    {
        $this->DI
            ->expects($this->once())
            ->method('getUrlRouteAbsoluteDomain')
            ->with($routeConfig->route, array_merge($routeConfig->attributes, $routeConfig->query))
            ->willReturn(self::URL);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->jwtTokenExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->request)
            ->willReturn(self::JWT_TOKEN);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $routeConfig->method,
                self::URL,
                $this->callback(function (array $options) use ($routeConfig) {
                    if ('multipart/form-data' === $routeConfig->contentType) {
                        $this->assertArrayHasKey('body', $options);
                        $this->assertArrayHasKey('headers', $options);
                        $this->assertInstanceOf(\Generator::class, $options['body']);
                        $this->assertEquals('Content-Type: multipart/form-data', explode(';', end($options['headers']))[0]);
                        $this->assertCount(1 + count($routeConfig->headers) + count($routeConfig->cookies), $options['headers']);
                    }

                    if ('application/json' === $routeConfig->contentType) {
                        $this->assertArrayHasKey('json', $options);
                        $this->assertEquals($routeConfig->content, $options['json']);
                        $this->assertCount(count($routeConfig->headers) + count($routeConfig->cookies), $options['headers']);
                    }

                    foreach ($routeConfig->headers as $header) {
                        $this->assertContains($header, $options['headers']);
                    }

                    foreach ($routeConfig->cookies as $cookie) {
                        $this->assertContains($cookie, $options['headers']);
                    }

                    $this->assertArrayHasKey('proxy', $options);
                    $this->assertArrayHasKey('verify_peer', $options);
                    $this->assertArrayHasKey('verify_host', $options);
                    $this->assertArrayHasKey('auth_bearer', $options);
                    $this->assertEquals('http://proxy:80', $options['proxy']);
                    $this->assertFalse($options['verify_peer']);
                    $this->assertFalse($options['verify_host']);
                    $this->assertEquals(self::JWT_TOKEN, $options['auth_bearer']);

                    return true;
                }))
            ->willReturn($this->httpClientResponse);

        $this->httpClientResponse
           ->expects($this->atLeastOnce())
           ->method('getContent')
           ->with($this->callback(function (bool $throwException = true) {
               ++$this->getContentNumCalls;

               match ($this->getContentNumCalls) {
                   1 => $this->assertTrue($throwException),
                   default => $this->assertFalse($throwException)
               };

               return true;
           }))
           ->willReturnCallback(function () use ($requestException) {
               if (null === $requestException) {
                   return $this->responseContentExpected;
               }

               if (1 === $this->getContentNumCalls) {
                   throw $requestException;
               }

               return $this->responseContentExpected;
           });
    }

    /** @test */
    public function itShouldReturnAValidResponseDtoRequestApplicationJson(): void
    {
        $content = [
            'param1' => 'param1',
            'param2' => 'param2',
        ];
        $query = [
            'queryParam1' => 'queryParam1',
        ];
        $cookies = [
            new Cookie('cookie1', 'cookie1'),
            new Cookie('cookie2', 'cookie2'),
        ];
        $headers = [
            'header1',
            'header2',
        ];

        $routeConfig = ModuleCommunicationFactoryTest::json(true, $content, $query, [], $cookies, $headers);
        $this->mockRequestMethod($routeConfig);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    /** @test */
    public function itShouldReturnAValidResponseDtoRequestMultiPartFormData(): void
    {
        $content = [
            'param1' => 'param1',
            'param2' => 'param2',
        ];
        $query = [
            'queryParam1' => 'queryParam1',
        ];
        $cookies = [
            new Cookie('cookie1', 'cookie1'),
            new Cookie('cookie2', 'cookie2'),
        ];
        $headers = [
            'header1',
            'header2',
        ];
        $routeConfig = ModuleCommunicationFactoryTest::form(true, $content, $query, [], $cookies, $headers);
        $this->mockRequestMethod($routeConfig);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    /** @test */
    public function itShouldReturnAValidResponseDtoResponseContentEmpty(): void
    {
        $this->responseContentExpected = '';
        $content = [
            'param1' => 'param1',
            'param2' => 'param2',
        ];
        $query = [
            'queryParam1' => 'queryParam1',
        ];
        $cookies = [
            new Cookie('cookie1', 'cookie1'),
            new Cookie('cookie2', 'cookie2'),
        ];
        $headers = [
            'header1',
            'header2',
        ];
        $routeConfig = ModuleCommunicationFactoryTest::json(true, $content, $query, [], $cookies, $headers);
        $this->mockRequestMethod($routeConfig);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString(''), $return);
    }

    /** @test */
    public function itShouldFailWrongJsonInResponseContent(): void
    {
        $this->responseContentExpected = 'Wrong json';
        $content = [
            'param1' => 'param1',
            'param2' => 'param2',
        ];
        $query = [
            'queryParam1' => 'queryParam1',
        ];
        $cookies = [
            new Cookie('cookie1', 'cookie1'),
            new Cookie('cookie2', 'cookie2'),
        ];
        $headers = [
            'header1',
            'header2',
        ];
        $routeConfig = ModuleCommunicationFactoryTest::json(true, $content, $query, [], $cookies, $headers);
        $this->mockRequestMethod($routeConfig);
        $this->expectException(ModuleCommunicationException::class);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString(''), $return);
    }

    /** @test */
    public function itShouldFailResponseError400(): void
    {
        /** @var MockObject|HttpExceptionInterface $httpExceptionInterface */
        $httpExceptionInterface = $this->createMock(HttpExceptionInterface::class);

        $content = [
            'param1' => 'param1',
            'param2' => 'param2',
        ];
        $query = [
            'queryParam1' => 'queryParam1',
        ];
        $cookies = [
            new Cookie('cookie1', 'cookie1'),
            new Cookie('cookie2', 'cookie2'),
        ];
        $headers = [
            'header1',
            'header2',
        ];
        $routeConfig = ModuleCommunicationFactoryTest::json(true, $content, $query, [], $cookies, $headers);
        $this->mockRequestMethod($routeConfig, Error400Exception::fromMessage('', $httpExceptionInterface));

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    /** @test */
    public function itShouldFailResponseError500(): void
    {
        /** @var MockObject|HttpExceptionInterface $httpExceptionInterface */
        $httpExceptionInterface = $this->createMock(HttpExceptionInterface::class);

        $content = [
            'param1' => 'param1',
            'param2' => 'param2',
        ];
        $query = [
            'queryParam1' => 'queryParam1',
        ];
        $cookies = [
            new Cookie('cookie1', 'cookie1'),
            new Cookie('cookie2', 'cookie2'),
        ];
        $headers = [
            'header1',
            'header2',
        ];
        $routeConfig = ModuleCommunicationFactoryTest::json(true, $content, $query, [], $cookies, $headers);
        $this->mockRequestMethod($routeConfig, Error500Exception::fromMessage('', $httpExceptionInterface));

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    /** @test */
    public function itShouldFailResponseNetworkError(): void
    {
        /** @var MockObject|HttpExceptionInterface $httpExceptionInterface */
        $httpExceptionInterface = $this->createMock(HttpExceptionInterface::class);
        $this->expectException(ModuleCommunicationException::class);

        $content = [
            'param1' => 'param1',
            'param2' => 'param2',
        ];
        $query = [
            'queryParam1' => 'queryParam1',
        ];
        $cookies = [
            new Cookie('cookie1', 'cookie1'),
            new Cookie('cookie2', 'cookie2'),
        ];
        $headers = [
            'header1',
            'header2',
        ];
        $routeConfig = ModuleCommunicationFactoryTest::json(true, $content, $query, [], $cookies, $headers);
        $this->mockRequestMethod($routeConfig, NetworkException::fromMessage('', $httpExceptionInterface));

        $this->object->__invoke($routeConfig);
    }
}
