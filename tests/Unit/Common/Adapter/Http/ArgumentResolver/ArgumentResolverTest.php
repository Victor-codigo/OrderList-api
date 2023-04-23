<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\ArgumentResolver\ArgumentResolver;
use Common\Adapter\Http\ArgumentResolver\Exception\InvalidJsonException;
use Common\Adapter\Http\ArgumentResolver\Exception\InvalidMimeTypeException;
use Common\Adapter\Http\ArgumentResolver\RequestValidation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures\CustomRequestDto;
use Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures\CustomRequestNoInterfaceDto;

class ArgumentResolverTest extends TestCase
{
    private ArgumentResolver $object;
    private RequestValidation $requestValidation;
    private Request $request;
    private MockObject|ArgumentMetadata $argumentMetaData;

    public function setUp(): void
    {
        $this->argumentMetaData = $this->createPartialMock(ArgumentMetadata::class, ['getType']);
        $this->request = Request::create('', 'POST', [], [], [], [], '{"key":"value"}');
        $this->requestValidation = new RequestValidation();
        $this->object = new ArgumentResolver($this->requestValidation);
    }

    /** @test */
    public function resolveRequestSupportError(): void
    {
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(\stdClass::class);

        $return = $this->object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveRequestSupportGetTypeNullError(): void
    {
        $this->argumentMetaData
            ->expects($this->exactly(1))
            ->method('getType')
            ->willReturn(null);

        $return = $this->object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveRequestSupportInterfaceError(): void
    {
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestNoInterfaceDto::class);

        $return = $this->object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveValidationOk(): void
    {
        $this->argumentMetaData
            ->expects($this->exactly(3))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->request->headers->set('Content-Type', 'application/json');
        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
            $this->assertInstanceOf(CustomRequestDto::class, $dto);
            $this->assertEquals($this->request, $dto->getRequest());
        }
    }

    /** @test */
    public function resolveValidationError(): void
    {
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->expectException(InvalidMimeTypeException::class);

        $this->request->headers->set('Content-Type', 'application/html');
        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveValidationAllowedContentNull(): void
    {
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->expectException(InvalidMimeTypeException::class);

        $this->request->headers->set('Content-Type', null);
        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveValidationContentJsonInvalid(): void
    {
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->expectException(InvalidJsonException::class);

        $this->request = Request::create('', 'POST', [], [], [], [], '{"key":"va');
        $this->request->headers->set('Content-Type', 'application/json');
        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveValidationGetRequestHasNotContentType(): void
    {
        $this->request = Request::create('', 'GET');
        $this->request->headers->set('Content-Type', null);

        $this->argumentMetaData
            ->expects($this->exactly(3))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }
}
