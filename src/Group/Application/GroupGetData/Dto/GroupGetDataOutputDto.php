<?php

declare(strict_types=1);

namespace Group\Application\GroupGetData\Dto;

class GroupGetDataOutputDto
{
    /**
     * @param \Generator<int, array{
     *  group_id: string|null,
     *  type: string,
     *  name: string|null,
     *  description: string|null,
     *  image: string|null,
     *  created_on: string
     * }> $data
     */
    public function __construct(
        public readonly \Generator $data,
    ) {
    }
}
