<?php

declare(strict_types=1);

namespace Product\Application\ProductSetShopPrice\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class ProductSetShopPriceInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $productsId;
    /**
     * @var string[]
     */
    public readonly array $shopsId;
    /**
     * @var Money[]
     */
    public readonly array $prices;

    /**
     * @param string[]|null $productsId
     * @param string[]|null $shopsId
     * @param float[]|null  $prices
     */
    public function __construct(UserShared $userSession, string|null $groupId, array|null $productsId, array|null $shopsId, array|null $prices)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->productsId = array_map(
            fn (string $productId) => ValueObjectFactory::createIdentifier($productId),
            $productsId ?? []
        );
        $this->shopsId = array_map(
            fn (string $shopId) => ValueObjectFactory::createIdentifier($shopId),
            $shopsId ?? []
        );
        $this->prices = array_map(
            fn (float|null $price) => ValueObjectFactory::createMoney($price),
            $prices ?? []
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);

        $errorListProductsId = $this->validateProductsId($validator);
        $errorListShopsId = $this->validateShopsId($validator);
        $errorListPrices = $this->validatePrices($validator);

        if (!empty($errorListProductsId)) {
            $errorList['products_id'] = $errorListProductsId;
        }

        if (!empty($errorListShopsId)) {
            $errorList['shops_id'] = $errorListShopsId;
        }

        if (!empty($errorListPrices)) {
            $errorList['prices'] = $errorListPrices;
        }

        if ((count($this->productsId) !== count($this->shopsId)) || (count($this->shopsId) !== count($this->prices))) {
            $errorList['shops_prices_not_equal'] = [VALIDATION_ERRORS::NOT_EQUAL_TO];
        }

        return $errorList;
    }

    private function validateProductsId(ValidationInterface $validator): array
    {
        $errorListProductsId = $validator
            ->setValue($this->productsId)
            ->notBlank()
            ->validate();

        $errorListProductsId = array_merge(
            $errorListProductsId,
            $validator->validateValueObjectArray($this->productsId)
        );

        return $errorListProductsId;
    }

    private function validateShopsId(ValidationInterface $validator): array
    {
        $errorListShopsId = $validator
            ->setValue($this->shopsId)
            ->notBlank()
            ->validate();

        $errorListShopsId = array_merge(
            $errorListShopsId,
            $validator->validateValueObjectArray($this->shopsId)
        );

        return $errorListShopsId;
    }

    private function validatePrices(ValidationInterface $validator): array
    {
        $errorListPrices = $validator
            ->setValue($this->prices)
            ->notBlank()
            ->validate();

        $errorListPrices = array_merge(
            $errorListPrices,
            $validator->validateValueObjectArray($this->prices)
        );

        return $errorListPrices;
    }
}
