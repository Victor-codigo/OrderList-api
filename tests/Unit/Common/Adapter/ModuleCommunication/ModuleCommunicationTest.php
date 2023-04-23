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
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Test\Unit\Common\Adapter\ModuleCommunication\Fixtures\ModuleCommunicationFactoryTest;

class ModuleCommunicationTest extends TestCase
{
    private const USER_ID = '96eb8c18-c11b-4003-a1d3-f884d4f24163';
    private const URL = 'http://domain.com/url/to/resource';
    private const JWT_TOKEN = 'asdgasdfbadsfhaetrhadfhbadfhbaerth';

    private ModuleCommunication $object;
    private MockObject|HttpClientInterface $httpClient;
    private MockObject|HttpClientResponseInterface  $httpClientResponse;
    private MockObject|DIInterface $DI;
    private MockObject|JWTManager $jwtManager;
    private MockObject|Security $security;
    private MockObject|UserInterface $user;
    private string $appEnv;
    private string $responseContentExpected;
    private int $getContentNumCalls;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientResponse = $this->createMock(HttpClientResponseInterface::class);
        $this->DI = $this->createMock(DIInterface::class);
        $this->jwtManager = $this->createMock(JWTManager::class);
        $this->security = $this->createMock(Security::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->appEnv = 'prod';
        $this->responseContentExpected = json_encode($this->getResponseDefaultDto());
        $this->getContentNumCalls = 0;

        $this->object = new ModuleCommunication($this->httpClient, $this->DI, $this->jwtManager, $this->security, $this->appEnv);
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

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->jwtManager
            ->expects($this->once())
            ->method('createFromPayload')
            ->with($this->user)
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
                        $this->assertInstanceOf(\Generator::class, $options['body']);
                        $this->assertEquals('Content-Type: multipart/form-data', explode(';', $options['headers'][0])[0]);
                    }

                    if ('application/json' === $routeConfig->contentType) {
                        $this->assertArrayHasKey('json', $options);
                        $this->assertEquals($routeConfig->content, $options['json']);
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
        $routeConfig = ModuleCommunicationFactoryTest::json($content, $query, [], true);
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
        $routeConfig = ModuleCommunicationFactoryTest::form($content, $query, [], [], true);
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
        $routeConfig = ModuleCommunicationFactoryTest::json($content, $query, [], true);
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
        $routeConfig = ModuleCommunicationFactoryTest::json($content, $query, [], true);
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
        $routeConfig = ModuleCommunicationFactoryTest::json($content, $query, [], true);
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
        $routeConfig = ModuleCommunicationFactoryTest::json($content, $query, [], true);
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
        $routeConfig = ModuleCommunicationFactoryTest::json($content, $query, [], true);
        $this->mockRequestMethod($routeConfig, NetworkException::fromMessage('', $httpExceptionInterface));

        $this->object->__invoke($routeConfig);
    }
}
