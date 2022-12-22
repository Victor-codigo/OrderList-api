<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver;

use Common\Domain\Exception\InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestValidation
{
    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Request $request): void
    {
        $this->validateContentType($request);

        try {
            $request->request = $this->createParams($request);
        } catch (JsonException) {
            throw InvalidArgumentException::fromMessage('Invalid JSON');
        }
    }

    /**
     * @throws \JsonException
     */
    private function createParams(Request $request): ParameterBag
    {
        return match ($this->getContentType($request)) {
            REQUEST_ALLOWED_CONTENT::JSON->value => $this->applicationJson($request),
            REQUEST_ALLOWED_CONTENT::FORM_DATA->value => $request->request
        };
    }

    private function applicationJson(Request $request): ParameterBag
    {
        $params = (array) json_decode(
            $request->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return new ParameterBag($params);
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
            throw InvalidArgumentException::fromMessage(sprintf('Content-Type [%s] is not allowed. Only [%s] are allowed.', $request->getContentType(), implode(', ', array_column(REQUEST_ALLOWED_CONTENT::cases(), 'value'))));
        }
    }
}
