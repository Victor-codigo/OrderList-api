<?php

declare(strict_types=1);

namespace Common\Adapter\Http\RequestDataValidation;

trait RequestDataValidation
{
    /**
     * @param string[]|int[]|float[]|null $values
     *
     * @return string[]|int[]|float[]|null
     */
    private function validateArrayOverflow(?array $values, int $valuesMax): ?array
    {
        if (null === $values) {
            return null;
        }

        if (count($values) > $valuesMax) {
            return array_slice($values, 0, $valuesMax);
        }

        return $values;
    }

    /**
     * @return string[]|int[]|float[]|null $values
     */
    private function validateCsvOverflow(?string $values, int $valuesMax): ?array
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
     * @param mixed                       $default value to be set, to those values that are not float
     * @param string[]|int[]|float[]|null $values  $values
     *
     * @return mixed[]
     */
    private function arrayFilterFloat(?array $values, int $valuesMax, mixed $default = null): ?array
    {
        if (null === $values) {
            return null;
        }

        $valuesValid = $this->validateArrayOverflow($values, $valuesMax);

        return array_map(
            fn ($value): mixed => false !== filter_var($value, FILTER_VALIDATE_FLOAT)
                ? (float) $value
                : $default,
            $valuesValid ?? []
        );
    }
}
