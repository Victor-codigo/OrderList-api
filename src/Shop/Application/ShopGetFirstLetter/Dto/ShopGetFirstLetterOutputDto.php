<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetFirstLetter\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

readonly class ShopGetFirstLetterOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array<int, string> $shopsFirstLetter
     */
    public function __construct(
        public array $shopsFirstLetter
    ) {
    }

    public function toArray(): array
    {
        return $this->shopsFirstLetter;
    }
}
