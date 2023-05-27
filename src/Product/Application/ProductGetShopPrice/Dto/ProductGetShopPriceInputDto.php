<?php

declare(strict_types=1);

namespace Product\Application\ProductGetShopPrice\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ProductGetShopPriceInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     * */
    public readonly array $productsId;
    /**
     * @var Identifier[]
     */
    public readonly array $shopsId;
    public readonly Identifier $groupId;

    public function __construct(UserShared $userSession, array|null $productsId, array|null $shopsId, string|null $groupId)
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
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorListGroupId = $validator->validateValueObject($this->groupId);
        if (!empty($errorListGroupId)) {
            $errorListGroupId = ['group_id' => $errorListGroupId];
        }

        $errorListProductsId = $this->validateProductsId($validator);
        $errorListShopsId = $this->validateShopsId($validator);

        $errorListProductsIdEmpty = !array_key_exists('products_id_empty', $errorListProductsId);
        $errorListShopsIdEmpty = !array_key_exists('shops_id_empty', $errorListShopsId);

        if ($errorListProductsIdEmpty && !$errorListShopsIdEmpty) {
            unset($errorListShopsId['shops_id_empty']);
        } elseif (!$errorListProductsIdEmpty && $errorListShopsIdEmpty) {
            unset($errorListProductsId['products_id_empty']);
        }

        return array_merge($errorListGroupId, $errorListProductsId, $errorListShopsId);
    }

    private function validateProductsId(ValidationInterface $validator): array
    {
        $errorListProductsIdEmpty = $validator
            ->setValue($this->productsId)
            ->notBlank()
            ->validate();

        if (!empty($errorListProductsIdEmpty)) {
            return ['products_id_empty' => $errorListProductsIdEmpty];
        }

        $errorListProductsId = $validator->validateValueObjectArray($this->productsId);
        if (!empty($errorListProductsId)) {
            $errorListProductsId = ['products_id' => $errorListProductsId];
        }

        return $errorListProductsId;
    }

    private function validateShopsId(ValidationInterface $validator): array
    {
        $errorListShopsIdEmpty = $validator
            ->setValue($this->shopsId)
            ->notBlank()
            ->validate();

        if (!empty($errorListShopsIdEmpty)) {
            return ['shops_id_empty' => $errorListShopsIdEmpty];
        }

        $errorListShopsId = $validator->validateValueObjectArray($this->shopsId);
        if (!empty($errorListShopsId)) {
            $errorListShopsId = ['shops_id' => $errorListShopsId];
        }

        return $errorListShopsId;
    }
}
