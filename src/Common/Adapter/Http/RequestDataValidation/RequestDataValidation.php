<?php

declare(strict_types=1);

namespace Common\Adapter\Http\RequestDataValidation;

trait RequestDataValidation
{
    private function validateArrayOverflow(array|null $values, int $valuesMax): array|null
    {
        if (null === $values) {
            return null;
        }

        if (count($values) > $valuesMax) {
            return array_slice($values, 0, $valuesMax);
        }

        return $values;
    }
}
