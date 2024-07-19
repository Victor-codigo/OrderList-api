<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\ArgumentResolver\ArgumentResolver;
use Common\Adapter\Http\ArgumentResolver\Exception\InvalidJsonException;
use Common\Adapter\Http\ArgumentResolver\Exception\InvalidMimeTypeException;
use Common\Adapter\Http\ArgumentResolver\RequestValidation;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures\CustomRequestDto;
use Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures\CustomRequestDtoThrowException;
use Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures\CustomRequestNoInterfaceDto;

class ArgumentResolverTest extends TestCase
{
    private ArgumentResolver $object;
    private RequestValidation $requestValidation;
    private Request $request;
    private MockObject|ArgumentMetadata $argumentMetaData;

    #[\Override]
    public function setUp(): void
    {
        $this->argumentMetaData = $this->createPartialMock(ArgumentMetadata::class, ['getType', 'getName']);
        $this->request = Request::create('', 'POST', [], [], [], [], '{"key":"value"}');
        $this->requestValidation = new RequestValidation();
    }

    /** @test */
    public function resolveRequestSupportError(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(1))
            ->method('getName')
            ->willReturn('some name');

        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(\stdClass::class);

        $return = $object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveRequestSupportGetTypeNullError(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(1))
            ->method('getType')
            ->willReturn(null);

        $return = $object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveRequestSupportErrorNelmioApiBundleHackInDebugMode(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(1))
            ->method('getName')
            ->willReturn('area');

        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn('string');

        $return = $object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveRequestSupportErrorNelmioApiBundleHackNotInDebugMode(): void
    {
        $object = new ArgumentResolver($this->requestValidation, false);
        $this->argumentMetaData
            ->expects($this->never())
            ->method('getName')
            ->willReturn('area');

        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn('string');

        $this->expectException(\ReflectionException::class);
        $return = $object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveRequestSupportInterfaceError(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestNoInterfaceDto::class);

        $return = $object->resolve($this->request, $this->argumentMetaData);

        $this->assertEmpty(iterator_to_array($return));
    }

    /** @test */
    public function resolveValidationOk(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(3))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->request->headers->set('Content-Type', 'application/json');
        foreach ($object->resolve($this->request, $this->argumentMetaData) as $dto) {
            $this->assertInstanceOf(CustomRequestDto::class, $dto);
            $this->assertEquals($this->request, $dto->getRequest());
        }
    }

    /** @test */
    public function resolveValidationError(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->expectException(InvalidMimeTypeException::class);

        $this->request->headers->set('Content-Type', 'application/html');
        foreach ($object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveValidationAllowedContentNull(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->expectException(InvalidMimeTypeException::class);

        $this->request->headers->set('Content-Type', null);
        foreach ($object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveValidationContentJsonInvalid(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(2))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->expectException(InvalidJsonException::class);

        $this->request = Request::create('', 'POST', [], [], [], [], '{"key":"va');
        $this->request->headers->set('Content-Type', 'application/json');
        foreach ($object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveValidationGetRequestHasNotContentType(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->request = Request::create('', 'GET');
        $this->request->headers->set('Content-Type', null);

        $this->argumentMetaData
            ->expects($this->exactly(3))
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        foreach ($object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveDtoThrowsAnException(): void
    {
        $object = new ArgumentResolver($this->requestValidation, true);
        $this->argumentMetaData
            ->expects($this->exactly(3))
            ->method('getType')
            ->willReturn(CustomRequestDtoThrowException::class);


        $this->request->headers->set('Content-Type', 'application/json');

        $this->expectException(Exception::class);
        foreach ($object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }
}
