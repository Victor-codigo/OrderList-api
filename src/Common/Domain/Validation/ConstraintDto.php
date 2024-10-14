<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

use Common\Domain\Validation\Common\CONSTRAINTS_NAMES;

class ConstraintDto
{
    public readonly CONSTRAINTS_NAMES $type;

    /**
     * @var array<string, mixed>
     */
    public readonly array $params;

    /**
     * @param array<string, mixed>|null $params
     */
    public function __construct(CONSTRAINTS_NAMES $type, ?array $params)
    {
        $this->type = $type;

        if (null !== $params) {
            $this->params = $params;
        } else {
            $this->params = [];
        }
    }
}
