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
}
