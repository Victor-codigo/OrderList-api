<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\ArgumentResolver\RequestValidation;
use Common\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestValidationTest extends TestCase
{
    private RequestValidation $requestValidation;

    public function setUp(): void
    {
        $this->requestValidation = new RequestValidation();
    }

    /** @test */
    public function contentTypeIncorrect(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = Request::create(
            '',
            '',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{wrong JSON}'
        );

        $this->requestValidation->__invoke($request);
    }

    /** @test */
    public function errorCreatingJsonParams(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = Request::create(
            '',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{wrong JSON}'
        );

        $this->requestValidation->__invoke($request);
    }

    /** @test */
    public function responseErrorContentTypeNameWrong(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = Request::create(
            '',
            'GET',
            [],
            [],
            [],
            ['Content-Type' => 'application/json'],
            '{"key":"param"}'
        );

        $this->requestValidation->__invoke($request);
    }

    /** @test */
    public function responseOk(): void
    {
        $request = Request::create(
            '',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"key":"param"}'
        );

        $this->requestValidation->__invoke($request);

        $this->assertEquals('param', $request->get('key'),
            'RequestValidation: Error creating request content');
    }
}
