<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\ArgumentResolver\ArgumentResolver;
use Common\Adapter\Http\ArgumentResolver\RequestValidation;
use Common\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Test\Unit\Common\Adapter\Http\ArgumentResolver\Fixtures\CustomRequestDto;
use stdClass;

class ArgumentResolverTest extends TestCase
{
    private ArgumentResolver $object;
    private RequestValidation $requestValidation;
    private Request $request;
    private MockObject|ArgumentMetadata $argumentMetaData;

    public function setUp(): void
    {
        $this->argumentMetaData = $this->createPartialMock(ArgumentMetadata::class, ['getType']);
        $this->request = Request::create('', 'GET', [], [], [], [], '{"key":"value"}');
        $this->requestValidation = new RequestValidation();
        $this->object = new ArgumentResolver($this->requestValidation);
    }

    /** @test */
    public function supportsRequestOk(): void
    {
        $this->argumentMetaData
            ->expects($this->once())
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $return = $this->object->supports($this->request, $this->argumentMetaData);

        $this->assertTrue($return,
            'support: should not fail');
    }

    /** @test */
    public function supportsRequestError(): void
    {
        $this->argumentMetaData
            ->expects($this->once())
            ->method('getType')
            ->willReturn(stdClass::class);

        $return = $this->object->supports($this->request, $this->argumentMetaData);

        $this->assertFalse($return,
            'support: It was expected that it wasn\´t suported');
    }

    /** @test */
    public function resolveValidationOk(): void
    {
        $this->argumentMetaData
            ->expects($this->once())
            ->method('getType')
            ->willReturn(CustomRequestDto::class);

        $this->request->headers->set('Content-Type', 'application/json');
        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
            $this->assertInstanceOf(CustomRequestDto::class, $dto,
                'resolve: Class returned is worng');
            $this->assertEquals($this->request, $dto->getRequest(),
                'resolve: Request inserted in the Dto is wrong');
        }
    }

    /** @test */
    public function resolveValidationError(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->request->headers->set('Content-Type', 'application/html');
        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }

    /** @test */
    public function resolveValidationAllowedContentNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->request->headers->set('Content-Type', null);
        foreach ($this->object->resolve($this->request, $this->argumentMetaData) as $dto) {
        }
    }
}
