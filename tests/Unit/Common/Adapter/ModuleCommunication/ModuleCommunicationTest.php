<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\ModuleCommunication;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationErrorResponseException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationTokenNotFoundInRequestException;
use Common\Adapter\ModuleCommunication\ModuleCommunication;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\HttpClient\Exception\Error500Exception;
use Common\Domain\HttpClient\Exception\NetworkException;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDtoPaginatorInterface;
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
use Test\Unit\Common\Adapter\ModuleCommunication\Fixtures\AUTHENTICATION_SOURCE;
use Test\Unit\Common\Adapter\ModuleCommunication\Fixtures\ModuleCommunicationConfigTestDto;
use Test\Unit\Common\Adapter\ModuleCommunication\Fixtures\ModuleCommunicationFactoryTest;

class ModuleCommunicationTest extends TestCase
{
    private const string URL = 'http://domain.com/url/to/resource';
    private const string JWT_TOKEN = 'asdgasdfbadsfhaetrhadfhbadfhbaerth';

    private ModuleCommunication $object;
    private MockObject|HttpClientInterface $httpClient;
    private MockObject|HttpClientResponseInterface $httpClientResponse;
    private MockObject|DIInterface $DI;
    private MockObject|TokenExtractorInterface $jwtTokenExtractor;
    private MockObject|RequestStack $requestStack;
    private MockObject|Request $request;
    private string $appEnv;
    private string $responseContentExpected;
    private int $getContentNumCalls;

    #[\Override]
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

    private function assertRequestOptionsIsOk(array $options, ModuleCommunicationConfigDto $routeConfig): void
    {
        if ('multipart/form-data' === $routeConfig->contentType) {
            $this->assertArrayHasKey('body', $options);
            $this->assertArrayHasKey('headers', $options);
            $this->assertInstanceOf(\Generator::class, $options['body']);
            $this->assertEquals('Content-Type: multipart/form-data', explode(';', (string) end($options['headers']))[0]);
            $this->assertCount(1 + count($routeConfig->headers) + count($routeConfig->cookies), $options['headers']);
        }

        if ('application/json' === $routeConfig->contentType) {
            if (!empty($routeConfig->content)) {
                $this->assertArrayHasKey('json', $options);
                $this->assertEquals($routeConfig->content, $options['json']);
            }

            if (!empty($routeConfig->headers) || !empty($routeConfig->cookies)) {
                $this->assertCount(count($routeConfig->headers) + count($routeConfig->cookies), $options['headers']);
            }
        }

        foreach ($routeConfig->headers as $header) {
            $this->assertContains($header, $options['headers']);
        }

        foreach ($routeConfig->cookies as $cookie) {
            $this->assertContains($cookie, $options['headers']);
        }

        $this->assertArrayHasKey('auth_bearer', $options);
        $this->assertEquals(self::JWT_TOKEN, $options['auth_bearer']);
    }

    private function mockRequestMethod(ModuleCommunicationConfigDto $routeConfig, string $url, string $expectedUrl, AUTHENTICATION_SOURCE $authenticationSource, ?\DomainException $requestException = null): void
    {
        $this->DI
            ->expects($this->once())
            ->method('getUrlRouteAbsoluteDomain')
            ->with($routeConfig->route, array_merge($routeConfig->attributes, $routeConfig->query))
            ->willReturn($url);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->jwtTokenExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->request)
            ->willReturn(AUTHENTICATION_SOURCE::REQUEST === $authenticationSource ? self::JWT_TOKEN : false);

        $this->httpClient
            ->expects(
                AUTHENTICATION_SOURCE::NOT_AUTHENTICATED === $authenticationSource && $routeConfig->authentication
                    ? $this->never() : $this->once()
            )
            ->method('request')
            ->with(
                $routeConfig->method,
                $this->equalTo($expectedUrl),
                $this->callback(fn (array $options): true => $this->assertRequestOptionsIsOk($options, $routeConfig) || true)
            )
            ->willReturn($this->httpClientResponse);

        $this->httpClientResponse
           ->expects(
               AUTHENTICATION_SOURCE::NOT_AUTHENTICATED === $authenticationSource && $routeConfig->authentication
                   ? $this->never() : $this->atLeastOnce()
           )
           ->method('getContent')
           ->with($this->callback(function (bool $throwException = true): bool {
               ++$this->getContentNumCalls;

               match ($this->getContentNumCalls) {
                   1 => $this->assertTrue($throwException),
                   default => $this->assertFalse($throwException)
               };

               return true;
           }))
           ->willReturnCallback(function () use ($requestException): string {
               if (null === $requestException) {
                   return $this->responseContentExpected;
               }

               if (1 === $this->getContentNumCalls) {
                   throw $requestException;
               }

               return $this->responseContentExpected;
           });
    }

    #[Test]
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
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    #[Test]
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
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    #[Test]
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
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString(''), $return);
    }

    #[Test]
    public function itShouldCreateAValidRequestNoDevOrTestUrlWithoutQueryString(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(true);
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST);

        $this->object->__invoke($routeConfig);
    }

    #[Test]
    public function itShouldCreateAValidRequestDevUrlWithoutQueryString(): void
    {
        $url = self::URL;
        $urlExpected = "{$url}?XDEBUG_SESSION=VSCODE";
        $routeConfig = ModuleCommunicationFactoryTest::json(true);

        $this->object = new ModuleCommunication(
            $this->httpClient,
            $this->DI,
            $this->jwtTokenExtractor,
            $this->requestStack,
            'dev'
        );

        $this->mockRequestMethod($routeConfig, $url, $urlExpected, AUTHENTICATION_SOURCE::REQUEST);

        $this->object->__invoke($routeConfig);
    }

    #[Test]
    public function itShouldCreateAValidRequestTestUrlWithoutQueryString(): void
    {
        $url = self::URL;
        $urlExpected = "{$url}?XDEBUG_SESSION=VSCODE&env=test";
        $routeConfig = ModuleCommunicationFactoryTest::json(true);

        $this->object = new ModuleCommunication(
            $this->httpClient,
            $this->DI,
            $this->jwtTokenExtractor,
            $this->requestStack,
            'test'
        );

        $this->mockRequestMethod($routeConfig, $url, $urlExpected, AUTHENTICATION_SOURCE::REQUEST);

        $this->object->__invoke($routeConfig);
    }

    #[Test]
    public function itShouldCreateAValidRequestTestUrlWithQueryString(): void
    {
        $url = self::URL.'?param1=value1&param2=value2';
        $urlExpected = self::URL.'?XDEBUG_SESSION=VSCODE&env=test&param1=value1&param2=value2';
        $routeConfig = ModuleCommunicationFactoryTest::json(true);

        $this->object = new ModuleCommunication(
            $this->httpClient,
            $this->DI,
            $this->jwtTokenExtractor,
            $this->requestStack,
            'test'
        );

        $this->mockRequestMethod($routeConfig, $url, $urlExpected, AUTHENTICATION_SOURCE::REQUEST);

        $this->object->__invoke($routeConfig);
    }

    #[Test]
    public function itShouldCreateAValidRequestAuthenticationPassedOnHeaders(): void
    {
        $url = self::URL.'?param1=value1&param2=value2';
        $urlExpected = self::URL.'?XDEBUG_SESSION=VSCODE&env=test&param1=value1&param2=value2';
        $routeConfig = ModuleCommunicationFactoryTest::json(true, [], [], [], [], [
            'Authorization' => 'Bearer '.self::JWT_TOKEN,
        ]);

        $this->object = new ModuleCommunication(
            $this->httpClient,
            $this->DI,
            $this->jwtTokenExtractor,
            $this->requestStack,
            'test'
        );

        $this->mockRequestMethod($routeConfig, $url, $urlExpected, AUTHENTICATION_SOURCE::PASS_ON_HEADERS);

        $this->object->__invoke($routeConfig);
    }

    #[Test]
    public function itShouldFailCreatingAValidRequestAuthenticationPassedOnHeadersHeaderBadFormed(): void
    {
        $url = self::URL.'?param1=value1&param2=value2';
        $urlExpected = self::URL.'?XDEBUG_SESSION=VSCODE&env=test&param1=value1&param2=value2';
        $routeConfig = ModuleCommunicationFactoryTest::json(true, [], [], [], [], [
            'Authorization' => self::JWT_TOKEN,
        ]);

        $this->object = new ModuleCommunication(
            $this->httpClient,
            $this->DI,
            $this->jwtTokenExtractor,
            $this->requestStack,
            'test'
        );

        $this->mockRequestMethod($routeConfig, $url, $urlExpected, AUTHENTICATION_SOURCE::NOT_AUTHENTICATED);

        $this->expectException(ModuleCommunicationTokenNotFoundInRequestException::class);
        $this->object->__invoke($routeConfig);
    }

    #[Test]
    public function itShouldFailCreatingAValidRequestTokenNotFoundInRequest(): void
    {
        $url = self::URL.'?param1=value1&param2=value2';
        $urlExpected = self::URL.'?XDEBUG_SESSION=VSCODE&env=test&param1=value1&param2=value2';
        $routeConfig = ModuleCommunicationFactoryTest::json(true);

        $this->object = new ModuleCommunication(
            $this->httpClient,
            $this->DI,
            $this->jwtTokenExtractor,
            $this->requestStack,
            'test'
        );

        $this->mockRequestMethod($routeConfig, $url, $urlExpected, AUTHENTICATION_SOURCE::NOT_AUTHENTICATED);

        $this->expectException(ModuleCommunicationTokenNotFoundInRequestException::class);
        $this->object->__invoke($routeConfig);
    }

    #[Test]
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
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST);
        $this->expectException(ModuleCommunicationException::class);

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString(''), $return);
    }

    #[Test]
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
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST, Error400Exception::fromMessage('', $httpExceptionInterface));

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    #[Test]
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
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST, Error500Exception::fromMessage('', $httpExceptionInterface));

        $return = $this->object->__invoke($routeConfig);

        $this->assertEquals($this->getResponseDtoFromString($this->responseContentExpected), $return);
    }

    #[Test]
    public function itShouldFailResponseNetworkError(): void
    {
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
        $this->mockRequestMethod($routeConfig, self::URL, self::URL, AUTHENTICATION_SOURCE::REQUEST, NetworkException::fromMessage(''));

        $this->object->__invoke($routeConfig);
    }

    #[Test]
    public function itShouldGetARangeOfPages(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );

        $responseExpected = new ResponseDto([
            'page' => 1,
            'pages_total' => 5,
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ]);

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->exactly(4);
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturn($responseExpected);

        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, 5);

        foreach ($return as $response) {
            $this->assertEquals($responseExpected->data, $response);
        }
    }

    #[Test]
    public function itShouldGetARangeOfPagesNoContent(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );

        $responseExpected = new ResponseDto(
            [],
            [],
            '',
            RESPONSE_STATUS::OK,
            false,
        );

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->once();
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturn($responseExpected);

        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, 5);

        foreach ($return as $response) {
            $this->assertEquals($responseExpected->data, $response);
        }
    }

    #[Test]
    public function itShouldGetARangeOfPagesNoPageEndSet(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );

        $responseExpected = new ResponseDto([
            'page' => 1,
            'pages_total' => 5,
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ]);

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->exactly(4);
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturn($responseExpected);

        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, null);

        foreach ($return as $response) {
            $this->assertEquals($responseExpected->data, $response);
        }
    }

    #[Test]
    public function itShouldGetARangeOfPagesPageEndIsBiggerThanPagesTotal(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );

        $responseExpected = new ResponseDto([
            'page' => 1,
            'pages_total' => 5,
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ]);

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->exactly(4);
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturn($responseExpected);

        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, 6);

        foreach ($return as $response) {
            $this->assertEquals($responseExpected->data, $response);
        }
    }

    #[Test]
    public function itShouldGetARangeOfPagesPagesTotalPathIsDeeperThanOne(): void
    {
        $routeConfig = ModuleCommunicationConfigTestDto::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );
        $routeConfig->setResponsePagesTotalPath('page.data.pages_total');

        $responseExpected = new ResponseDto([
            'page' => [
                'data' => [
                    'page' => 2,
                    'pages_total' => 5,
                ],
            ],
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ]);

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->exactly(4);
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturn($responseExpected);

        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, 5);

        foreach ($return as $response) {
            $this->assertEquals($responseExpected->data, $response);
        }
    }

    #[Test]
    public function itShouldFailPageIniIsLessThanZero(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMock
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(InvalidArgumentException::class);
        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 0, 5);

        foreach ($return as $response) {
        }
    }

    #[Test]
    public function itShouldFailResponseStatusIsNotOk(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );

        $responseExpected = new ResponseDto([
            'page' => 1,
            'pages_total' => 5,
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ]);
        $responseExpectedError = new ResponseDto([
            'page' => 1,
            'pages_total' => 5,
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ],
            [
                'error' => 'description',
            ],
            '',
            RESPONSE_STATUS::ERROR
        );

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->exactly(3);
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $responseExpected,
                $responseExpected,
                $responseExpectedError
            );

        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, 6);

        try {
            foreach ($return as $response) {
            }

            throw new \LogicException('No exceptions thrown: it should have thrown [ModuleCommunicationErrorResponseException]');
        } catch (ModuleCommunicationErrorResponseException $e) {
            $this->assertEquals($responseExpectedError->getErrors(), $e->getResponseErrors());
        }
    }

    #[Test]
    public function itShouldFailResponseHasErrors(): void
    {
        $routeConfig = ModuleCommunicationFactoryTest::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );

        $responseExpected = new ResponseDto([
            'page' => 1,
            'pages_total' => 5,
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ]);
        $responseExpectedError = new ResponseDto([
            'page' => 1,
            'pages_total' => 5,
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ],
            [
                'error' => 'description',
            ],
            '',
            RESPONSE_STATUS::OK
        );

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->exactly(3);
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturnOnConsecutiveCalls(
                $responseExpected,
                $responseExpected,
                $responseExpectedError
            );

        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, 6);

        try {
            foreach ($return as $response) {
            }

            throw new \LogicException('No exceptions thrown: it should have thrown [ModuleCommunicationErrorResponseException]');
        } catch (ModuleCommunicationErrorResponseException $e) {
            $this->assertEquals($responseExpectedError->getErrors(), $e->getResponseErrors());
        }
    }

    #[Test]
    public function itShouldFailResponsePagesTotalPathIsWrong(): void
    {
        $routeConfig = ModuleCommunicationConfigTestDto::json(
            true,
            [],
            [
                'page' => 2,
                'page_items' => 10,
            ],
            [],
            [],
            []
        );
        $routeConfig->setResponsePagesTotalPath('page.wrong.pages_total');

        $responseExpected = new ResponseDto([
            'page' => [
                'data' => [
                    'page' => 2,
                    'pages_total' => 5,
                ],
            ],
            'data' => [
                'index1' => 'value',
                'index2' => 'value',
            ],
        ]);

        $objectMock = $this->createPartialMock(ModuleCommunication::class, [
            '__invoke',
        ]);

        $objectMockMatcher = $this->exactly(1);
        $objectMock
            ->expects($objectMockMatcher)
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDtoPaginatorInterface $routeConfigActual) use ($routeConfig, $objectMockMatcher): bool {
                $routeConfigExpected = $routeConfig->cloneWithPage($routeConfig->query['page'] + $objectMockMatcher->numberOfInvocations() - 1);

                $this->assertEquals($routeConfigExpected, $routeConfigActual);

                return true;
            }))
            ->willReturn($responseExpected);

        $this->expectException(InvalidArgumentException::class);
        $return = $objectMock->getPagesRangeEndpoint($routeConfig, 2, 5);

        foreach ($return as $response) {
            $this->assertEquals($responseExpected->data, $response);
        }
    }
}
