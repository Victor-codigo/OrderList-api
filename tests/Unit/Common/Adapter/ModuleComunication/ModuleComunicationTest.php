<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\ModuleComunication;

use Common\Adapter\ModuleComumication\Exception\ModuleComunicationException;
use Common\Adapter\ModuleComumication\ModuleComunication;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\HttpClient\Exception\Error500Exception;
use Common\Domain\HttpClient\Exception\NetworkException;
use Common\Domain\ModuleComumication\ModuleComunicationConfigDto;
use Common\Domain\ModuleComumication\ModuleComunicationFactory;
use Common\Domain\Ports\DI\DIInterface;
use Common\Domain\Ports\HttpClient\HttpClientInterface;
use Common\Domain\Ports\HttpClient\HttpClientResponseInteface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class ModuleComunicationTest extends TestCase
{
    private const USER_ID = '96eb8c18-c11b-4003-a1d3-f884d4f24163';
    private const URL = 'http://domain.com/url/to/resource';
    private const JWT_TOKEN = 'asdgasdfbadsfhaetrhadfhbadfhbaerth';

    private ModuleComunication $object;
    private MockObject|HttpClientInterface $httpClient;
    private MockObject|HttpClientResponseInteface  $httpClientResponse;
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
        $this->httpClientResponse = $this->createMock(HttpClientResponseInteface::class);
        $this->DI = $this->createMock(DIInterface::class);
        $this->jwtManager = $this->createMock(JWTManager::class);
        $this->security = $this->createMock(Security::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->appEnv = 'prod';
        $this->responseContentExpected = json_encode($this->getResponseDefaultDto());
        $this->getContentNumCalls = 0;

        $this->object = new ModuleComunication($this->httpClient, $this->DI, $this->jwtManager, $this->security, $this->appEnv);
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

    private function mockRequestMethod(ModuleComunicationConfigDto $routeConfig, \DomainException $requestException = null): void
    {
        $this->DI
            ->expects($this->once())
            ->method('getUrlRouteAbsoluteDomain')
            ->with($routeConfig->route, $routeConfig->parameters)
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
            ->with($routeConfig->method,
                self::URL, [
                    'proxy' => 'http://proxy:80',
                    'verify_peer' => false,
                    'verify_host' => false,
                    'json' => $routeConfig->parameters,
                    'auth_bearer' => self::JWT_TOKEN,
                ]
            )
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
    public function itShouldReturnAValidResponseDto(): void
    {
        $routeConfig = ModuleComunicationFactory::userGet([self::USER_ID]);
        $this->mockRequestMethod($routeConfig);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    /** @test */
    public function itShouldReturnAValidResponseDtoResponseContentEmpty(): void
    {
        $this->responseContentExpected = '';
        $routeConfig = ModuleComunicationFactory::userGet([self::USER_ID]);
        $this->mockRequestMethod($routeConfig);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString(''), $return);
    }

    /** @test */
    public function itShouldFailWrongJsonInResponseContent(): void
    {
        $this->responseContentExpected = 'Wrong json';
        $routeConfig = ModuleComunicationFactory::userGet([self::USER_ID]);
        $this->mockRequestMethod($routeConfig);
        $this->expectException(ModuleComunicationException::class);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString(''), $return);
    }

    /** @test */
    public function itShouldFailResponseError400(): void
    {
        /** @var MockObject|HttpExceptionInterface $httpExceptionInterface */
        $httpExceptionInterface = $this->createMock(HttpExceptionInterface::class);

        $routeConfig = ModuleComunicationFactory::userGet([self::USER_ID]);
        $this->mockRequestMethod($routeConfig, Error400Exception::fromMessage('', $httpExceptionInterface));

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    /** @test */
    public function itShouldFailResponseError500(): void
    {
        /** @var MockObject|HttpExceptionInterface $httpExceptionInterface */
        $httpExceptionInterface = $this->createMock(HttpExceptionInterface::class);

        $routeConfig = ModuleComunicationFactory::userGet([self::USER_ID]);
        $this->mockRequestMethod($routeConfig, Error500Exception::fromMessage('', $httpExceptionInterface));

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    /** @test */
    public function itShouldFailResponseNetworkError(): void
    {
        /** @var MockObject|HttpExceptionInterface $httpExceptionInterface */
        $httpExceptionInterface = $this->createMock(HttpExceptionInterface::class);
        $this->expectException(ModuleComunicationException::class);

        $routeConfig = ModuleComunicationFactory::userGet([self::USER_ID]);
        $this->mockRequestMethod($routeConfig, NetworkException::fromMessage('', $httpExceptionInterface));

        $this->object->__invoke($routeConfig);
    }
}
