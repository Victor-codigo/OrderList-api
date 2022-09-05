<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

class ConstraintDto
{
    public readonly CONSTRAINTS_NAMES $type;

    /**
     * @var array key = param name
     *            value = param value
     */
    public readonly array $params;

    /**
     * @var array key = param name
     *            value = param value
     */
    public function __construct(CONSTRAINTS_NAMES $type, array|null $params)
    {
        $this->type = $type;

        if (null !== $params) {
            $this->params = $params;
        } else {
            $this->params = [];
        }
    }
}
