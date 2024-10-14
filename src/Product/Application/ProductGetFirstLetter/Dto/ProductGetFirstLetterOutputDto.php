<?php

declare(strict_types=1);

namespace Product\Application\ProductGetFirstLetter\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

readonly class ProductGetFirstLetterOutputDto implements ApplicationOutputInterface
{
    /**
     * @param string[] $productsFirstLetter
     */
    public function __construct(
        public array $productsFirstLetter,
    ) {
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->productsFirstLetter;
    }
}
