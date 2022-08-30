<?php

declare(strict_types=1);

namespace Common\Adapter\Framework\Http\Request;

use Common\Adapter\Exception\InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestValidation
{
    public function execute(Request $request): void
    {
        $this->validateContentType($request);

        try {
            $request->request = $this->createParams($request);
        } catch (JsonException) {
            throw InvalidArgumentException::createFromMessage('Invalid JSON');
        }
    }

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

    private function validateContentType(Request $request): void
    {
        if (!REQUEST_ALLOWED_CONTENT::has($request->getContentType())) {
            throw InvalidArgumentException::createFromMessage(sprintf('Content-Type [%s] is not allowed. Only [$s] are allowed.', $request->getContentType(), implode(',', REQUEST_ALLOWED_CONTENT::cases())));
        }
    }
}
