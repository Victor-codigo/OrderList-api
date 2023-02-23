<?php

declare(strict_types=1);

namespace Group\Application\GroupGetData\Dto;

use Generator;

class GroupGetDataOutputDto
{
    public function __construct(
        public readonly Generator $data
    ) {
    }
}
