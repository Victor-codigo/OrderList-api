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
     * @throws JsonException
     */
    private function createParams(Request $request): ParameterBag
    {
        $params = (array) json_decode(
            $request->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return new ParameterBag($params);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateContentType(Request $request): void
    {
        if (!REQUEST_ALLOWED_CONTENT::allowed($request->headers->get('CONTENT_TYPE'))) {
            throw InvalidArgumentException::fromMessage(sprintf('Content-Type [%s] is not allowed. Only [%s] are allowed.', $request->getContentType(), implode(', ', array_column(REQUEST_ALLOWED_CONTENT::cases(), 'value'))));
        }
    }
}
