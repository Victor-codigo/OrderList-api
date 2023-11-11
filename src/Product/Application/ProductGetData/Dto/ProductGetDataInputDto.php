<?php

declare(strict_types=1);

namespace Product\Application\ProductGetData\Dto;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ProductGetDataInputDto implements ServiceInputDtoInterface
{
    private const PRODUCT_NAME_STARTS_BY_LENGTH_MAX = VALUE_OBJECTS_CONSTRAINTS::NAME_WITH_SPACES_MAX_LENGTH;

    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $productId;
    /**
     * @var Identifier[]
     */
    public readonly array $shopId;
    public readonly string|null $productNameStartsWith;
    public readonly NameWithSpaces $productName;

    public function __construct(string|null $groupId, array|null $productsId, array|null $shopsId, string|null $productNameStartsWith, string|null $productName)
    {
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->productNameStartsWith = $productNameStartsWith;
        $this->productName = ValueObjectFactory::createNameWithSpaces($productName);
        $this->productId = array_map(
            fn (string $productId) => ValueObjectFactory::createIdentifier($productId),
            $productsId ?? []
        );
        $this->shopId = array_map(
            fn (string $shopId) => ValueObjectFactory::createIdentifier($shopId),
            $shopsId ?? []
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray(['group_id' => $this->groupId]);
        $errorListProductsId = $validator->validateValueObjectArray($this->productId);
        $errorListShopsId = $validator->validateValueObjectArray($this->shopId);

        if (null !== $this->productNameStartsWith) {
            $errorListProductNameStartsWith = $validator
            ->setValue($this->productNameStartsWith)
            ->stringMax(self::PRODUCT_NAME_STARTS_BY_LENGTH_MAX)
            ->validate();
        }
        if (!$this->productName->isNull()) {
            $errorListProductName = $validator->validateValueObject($this->productName);
        }

        if (!empty($errorListProductName)) {
            $errorList['product_name'] = $errorListProductName;
        }

        if (!empty($errorListProductsId)) {
            $errorList['products_id'] = $errorListProductsId;
        }

        if (!empty($errorListShopsId)) {
            $errorList['shops_id'] = $errorListShopsId;
        }

        if (isset($errorListProductNameStartsWith) && !empty($errorListProductNameStartsWith)) {
            $errorList['product_name_starts_with'] = $errorListProductNameStartsWith;
        }

        return $errorList;
    }
}
