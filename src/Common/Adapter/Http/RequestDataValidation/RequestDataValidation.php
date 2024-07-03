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

    private function validateCsvOverflow(string|null $values, int $valuesMax): array|null
    {
        if (null === $values) {
            return null;
        }

        return $this->validateArrayOverflow(
            explode(',', $values, $valuesMax + 1),
            $valuesMax
        );
    }

    /**
     * @param mixed $default value to be set, to those values that are not float
     */
    private function arrayFilterFloat(array|null $values, int $valuesMax, mixed $default = null): array
    {
        $valuesValid = $this->validateArrayOverflow($values, $valuesMax);

        return array_map(
            fn ($value): mixed => filter_var($value, FILTER_VALIDATE_FLOAT)
                ? (float) $value
                : $default,
            $valuesValid ?? []
        );
    }
}
