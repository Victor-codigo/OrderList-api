<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\ArgumentResolver;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Http\ArgumentResolver\Exception\InvalidJsonException;
use Common\Adapter\Http\ArgumentResolver\Exception\InvalidMimeTypeException;
use Common\Adapter\Http\ArgumentResolver\RequestValidation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestValidationTest extends TestCase
{
    private RequestValidation $requestValidation;

    #[\Override]
    public function setUp(): void
    {
        $this->requestValidation = new RequestValidation();
    }

    #[Test]
    public function contentTypeIncorrect(): void
    {
        $this->expectException(InvalidJsonException::class);

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

    #[Test]
    public function errorCreatingJsonParams(): void
    {
        $this->expectException(InvalidJsonException::class);

        $request = Request::create(
            '',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{wrong JSON}'
        );

        $this->requestValidation->__invoke($request);
    }

    #[Test]
    public function responseErrorContentTypeNameWrong(): void
    {
        $this->expectException(InvalidMimeTypeException::class);

        $request = Request::create(
            '',
            'POST',
            [],
            [],
            [],
            ['Content-Type' => 'application/json'],
            '{"key":"param"}'
        );

        $this->requestValidation->__invoke($request);
    }

    #[Test]
    public function responseOk(): void
    {
        $request = Request::create(
            '',
            'POST',
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

    #[Test]
    public function responseGetRequestOk(): void
    {
        $request = Request::create(
            '',
            'GET',
            [],
            [],
            [],
            [],
            '{"key":"param"}'
        );

        $this->requestValidation->__invoke($request);

        $this->assertEmpty($request->request);
    }
}
