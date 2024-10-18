<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

readonly class ShareListOrdersGetDataOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * } $sharedListData
     */
    public function __construct(
        public array $sharedListData,
    ) {
    }

    /**
     * @return array<int, array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * }>
     */
    #[\Override]
    public function toArray(): array
    {
        return [$this->sharedListData];
    }
}
