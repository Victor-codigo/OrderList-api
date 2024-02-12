<?php

declare(strict_types=1);

namespace Product\Application\SetProductShopPrice\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class SetProductShopPriceInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly Identifier $productId;
    public readonly Identifier $shopId;
    /**
     * @var string[]
     */
    public readonly array $productsOrShopsId;
    /**
     * @var Money[]
     */
    public readonly array $prices;

    /**
     * @param float[]|null $prices
     */
    public function __construct(UserShared $userSession, string|null $groupId, string|null $productId, string|null $shopId, array|null $productsOrShopsId, array|null $prices)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->productId = ValueObjectFactory::createIdentifier($productId);
        $this->shopId = ValueObjectFactory::createIdentifier($shopId);
        $this->productsOrShopsId = array_map(
            fn (string $productOrShopId) => ValueObjectFactory::createIdentifier($productOrShopId),
            $productsOrShopsId ?? []
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

        if ($this->productId->isNull() && $this->shopId->isNull()) {
            $errorList['product_id_and_shop_id'] = [VALIDATION_ERRORS::NOT_NULL];
        }

        $errorListProductId = $this->validateProductId($validator);
        $errorListShopId = $this->validateShopId($validator);

        $errorListProductsOrShopsId = $this->validateProductsOrShops($validator);
        $errorListPrices = $this->validatePrices($validator);

        if (count($this->productsOrShopsId) !== count($this->prices)) {
            $errorList['products_or_shops_prices_not_equals'] = [VALIDATION_ERRORS::NOT_EQUAL_TO];
        }

        return array_merge($errorList, $errorListProductsOrShopsId, $errorListProductId, $errorListShopId, $errorListPrices);
    }

    /**
     * @return array<{product_id: string[]}>
     */
    private function validateProductId(ValidationInterface $validator): array
    {
        $errorList = [];
        $errorListProductId = [];
        if (!$this->productId->isNull()) {
            $errorListProductId = $validator->validateValueObject($this->productId);
        }

        if (!empty($errorListProductId)) {
            $errorList['product_id'] = $errorListProductId;
        }

        return $errorList;
    }

    /**
     * @return array<{shop_id: string[]}>
     */
    private function validateShopId(ValidationInterface $validator): array
    {
        $errorList = [];
        $errorListShopId = [];
        if (!$this->shopId->isNull()) {
            $errorListShopId = $validator->validateValueObject($this->shopId);
        }

        if (!empty($errorListShopId)) {
            $errorList['shop_id'] = $errorListShopId;
        }

        return $errorList;
    }

    private function validatePrices(ValidationInterface $validator): array
    {
        $errorList = [];
        $errorListPrices = $validator->validateValueObjectArray($this->prices);

        if (!empty($errorListPrices)) {
            $errorList['prices'] = $errorListPrices;
        }

        return $errorList;
    }

    private function validateProductsOrShops(ValidationInterface $validator): array
    {
        $errorList = [];
        $errorListProductsOrShops = $validator->validateValueObjectArray($this->productsOrShopsId);

        if (!empty($errorListProductsOrShops)) {
            $errorList['products_or_shops_id'] = $errorListProductsOrShops;
        }

        return $errorList;
    }
}
