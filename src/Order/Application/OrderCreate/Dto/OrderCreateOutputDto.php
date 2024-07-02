<?php

declare(strict_types=1);

namespace Order\Application\OrderCreate\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class OrderCreateOutputDto implements ApplicationOutputInterface
{
    /**
     * @param string[] $ordersId
     */
    public function __construct(
        public readonly array $ordersId
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->ordersId,
        ];
    }
}
