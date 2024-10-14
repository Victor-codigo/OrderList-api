<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver;

use Common\Adapter\Http\ArgumentResolver\Exception\InvalidJsonException;
use Common\Adapter\Http\ArgumentResolver\Exception\InvalidMimeTypeException;
use Common\Domain\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class RequestValidation
{
    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Request $request): void
    {
        try {
            if ('GET' === $request->getMethod()) {
                $request->request = new InputBag([]);

                return;
            }

            $this->validateContentType($request);
            $request->request = $this->createParams($request);
        } catch (\JsonException) {
            throw InvalidJsonException::fromMessage('Invalid JSON');
        }
    }

    /**
     * @return InputBag<bool|float|int|string>
     *
     * @throws \JsonException
     */
    private function createParams(Request $request): InputBag
    {
        return match ($this->getContentType($request)) {
            REQUEST_ALLOWED_CONTENT::JSON->value => $this->applicationJson($request),
            REQUEST_ALLOWED_CONTENT::FORM_DATA->value => $request->request,
        };
    }

    /**
     * @return InputBag<bool|float|int|string>
     */
    private function applicationJson(Request $request): InputBag
    {
        $params = (array) json_decode(
            $request->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return new InputBag($params);
    }

    private function getContentType(Request $request): string
    {
        $contentType = $request->headers->get('CONTENT_TYPE');

        if (null === $contentType) {
            return '';
        }

        $contentType = explode(';', $contentType);

        return $contentType[0];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateContentType(Request $request): void
    {
        $contentType = $this->getContentType($request);

        if (!REQUEST_ALLOWED_CONTENT::allowed($contentType)) {
            throw InvalidMimeTypeException::fromMessage(sprintf('Content-Type [%s] is not allowed. Only [%s] are allowed.', $request->getContentTypeFormat(), implode(', ', array_column(REQUEST_ALLOWED_CONTENT::cases(), 'value'))));
        }
    }
}
