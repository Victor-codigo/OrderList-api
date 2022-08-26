<?php

declare(strict_types=1);

namespace Adapter\Framework\Http\Request;

use Adapter\Exception\InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestValidation
{
    public function validateContentType(Request $request)
    {
        if (!$this->validContentType($request->getContentType())) {
            throw InvalidArgumentException::createFromMessage(sprintf('Content-Type [%s] is not allowed. Only [$s] are allowed.', $request->getContentType(), implode(',', REQUEST_ALLOWED_CONTENT::cases())));
        }

        try {
            $request->request = new ParameterBag(json_decode(
                $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            ));
        } catch (JsonException) {
            throw InvalidArgumentException::createFromMessage('Invalid JSON');
        }
    }

    private function validContentType(string $content)
    {
        return in_array(
            $content,
            REQUEST_ALLOWED_CONTENT::cases(),
            true
        );
    }
}
