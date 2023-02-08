<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Exception;

use Common\Adapter\Event\Exception\ExceptionEventSubscriber;
use Common\Adapter\Http\Exception\HttpResponseException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Test\Unit\Common\Adapter\Event\Exception\Fixtures\DomainExceptionOutputForTesting;

class ExceptionEventSubscriberTest extends TestCase
{
    private ExceptionEventSubscriber $object;
    private MockObject|KernelInterface $kernel;
    private MockObject|Request $request;
    private ExceptionEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ExceptionEventSubscriber();
        $this->kernel = $this->createMock(KernelInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->event = new ExceptionEvent($this->kernel, $this->request, HttpKernelInterface::MAIN_REQUEST, new Exception());
    }

    private function getResponseDto(): ResponseDto
    {
        return new ResponseDto(
            ['key1' => 'value1'],
            ['key1' => 'value1'],
            'response message',
            RESPONSE_STATUS::OK
        );
    }

    /** @test */
    public function itShouldReturnAn404Error(): void
    {
        $exception = new NotFoundHttpException('NotFoundHttpException');
        $this->event->setThrowable($exception);
        $this->object->__invoke($this->event);

        $response = $this->event->getResponse();
        /** @var ResponseDto $responseContent */
        $responseContent = json_decode($response->getContent(), false);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ExceptionEventSubscriber::ERROR_404_MESSAGE, $responseContent->message);
    }

    /** @test */
    public function itShouldReturnAn403Error(): void
    {
        $exception = new AccessDeniedHttpException('AccessDeniedHttpException');
        $this->event->setThrowable($exception);
        $this->object->__invoke($this->event);

        $response = $this->event->getResponse();
        /** @var ResponseDto $responseContent */
        $responseContent = json_decode($response->getContent(), false);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals(ExceptionEventSubscriber::ERROR_403_MESSAGE, $responseContent->message);
    }

    /** @test */
    public function itShouldReturnAn500Error(): void
    {
        $exception = new DomainInternalErrorException('DomainInternalErrorException');
        $this->event->setThrowable($exception);
        $this->object->__invoke($this->event);

        $response = $this->event->getResponse();
        /** @var ResponseDto $responseContent */
        $responseContent = json_decode($response->getContent(), false);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(ExceptionEventSubscriber::ERROR_500_MESSAGE, $responseContent->message);
    }

    /** @test */
    public function itShouldReturnHttpResponseExceptionThrownHttpResponseException(): void
    {
        $responseDto = $this->getResponseDto();
        $exception = (new HttpResponseException('HttpResponseException'))
            ->setResponseData($responseDto)
            ->setStatusCode(Response::HTTP_ACCEPTED);
        $this->event->setThrowable($exception);
        $this->object->__invoke($this->event);

        $response = $this->event->getResponse();
        /** @var ResponseDto $responseContent */
        $responseContent = json_decode($response->getContent(), false);

        $this->assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());
        $this->assertEquals($responseDto->getStatus()->value, $responseContent->status);
        $this->assertEquals($responseDto->getMessage(), $responseContent->message);
        $this->assertEquals((object) $responseDto->getData(), $responseContent->data);
        $this->assertEquals((object) $responseDto->getErrors(), $responseContent->errors);
    }

    /** @test */
    public function itShouldReturnHttpResponseExceptionThrownDomainExceptionOutput(): void
    {
        $expectedException = DomainExceptionOutputForTesting::fromMessage('DomainExceptionOutputForTesting');
        $this->event->setThrowable($expectedException);
        $this->object->__invoke($this->event);

        $response = $this->event->getResponse();
        /** @var ResponseDto $responseContent */
        $responseContent = json_decode($response->getContent(), false);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEquals($expectedException->getStatus()->value, $responseContent->status);
        $this->assertEquals($expectedException->getMessage(), $responseContent->message);
        $this->assertEmpty($responseContent->data);
        $this->assertEquals((object) $expectedException->getErrors(), $responseContent->errors);
    }

    /** @test */
    public function itShouldReturnHttpResponseExceptionThrownNotADomainExceptionOutput(): void
    {
        $expectedException = new Exception('Exception');
        $this->event->setThrowable($expectedException);
        $this->object->__invoke($this->event);

        $response = $this->event->getResponse();
        /** @var ResponseDto $responseContent */
        $responseContent = json_decode($response->getContent(), false);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertEquals('Internal server error', $responseContent->message);
        $this->assertEmpty($responseContent->data);
        $this->assertEquals((object) ['internal' => 'Internal server error'], $responseContent->errors);
    }
}
