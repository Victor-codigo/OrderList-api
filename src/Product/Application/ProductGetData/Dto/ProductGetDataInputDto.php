<?php

declare(strict_types=1);

namespace Product\Application\ProductGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;

class ProductGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $productsId;
    /**
     * @var Identifier[]
     */
    public readonly array $shopsId;
    public readonly NameWithSpaces $productName;

    public readonly Filter $productNameFilter;
    public readonly Filter $shopNameFilter;

    public readonly bool $orderAsc;

    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;

    /**
     * @param string[]|null $productsId
     * @param string[]|null $shopsId
     */
    public function __construct(
        ?string $groupId,
        ?array $productsId,
        ?array $shopsId,
        ?string $productName,

        ?string $productNameFilterType,
        ?string $productNameFilterValue,
        ?string $shopNameFilterType,
        ?string $shopNameFilterValue,

        bool $orderAsc,

        int $page,
        int $pageItems,
    ) {
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->productName = ValueObjectFactory::createNameWithSpaces($productName);
        $this->productsId = array_map(
            fn (string $productId): Identifier => ValueObjectFactory::createIdentifier($productId),
            $productsId ?? []
        );
        $this->shopsId = array_map(
            fn (string $shopId): Identifier => ValueObjectFactory::createIdentifier($shopId),
            $shopsId ?? []
        );

        $this->productNameFilter = new Filter(
            'product_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::tryFrom($productNameFilterType ?? '')),
            ValueObjectFactory::createNameWithSpaces($productNameFilterValue)
        );
        $this->shopNameFilter = new Filter(
            'shop_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::tryFrom($shopNameFilterType ?? '')),
            ValueObjectFactory::createNameWithSpaces($shopNameFilterValue)
        );
        $this->orderAsc = $orderAsc;
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
    }

    /**
     * @return array{}|array<string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray(['group_id' => $this->groupId]);
        $errorListProductsId = $validator->validateValueObjectArray($this->productsId);
        $errorListShopsId = $validator->validateValueObjectArray($this->shopsId);
        $errorListPage = $validator->validateValueObject($this->page);
        $errorListPageItems = $validator->validateValueObject($this->pageItems);
        $errorListProductNameFilter = $this->validateFilter($validator, $this->productNameFilter, 'product_name');
        $errorListShopNameFilter = $this->validateFilter($validator, $this->shopNameFilter, 'shop_name');

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

        if (!empty($errorListPage)) {
            $errorList['page'] = $errorListPage;
        }

        if (!empty($errorListPageItems)) {
            $errorList['page_items'] = $errorListPageItems;
        }

        if (!empty($errorListProductNameFilter)) {
            $errorList = array_merge($errorList, $errorListProductNameFilter);
        }

        if (!empty($errorListShopNameFilter)) {
            $errorList = array_merge($errorList, $errorListShopNameFilter);
        }

        return $errorList;
    }

    /**
     * @return array<string, VALIDATION_ERRORS[]>
     */
    private function validateFilter(ValidationInterface $validator, Filter $filter, string $errorPrefix): array
    {
        if ($filter->getFilter()->isNull()
        && $filter->isNull()) {
            return [];
        }

        $errorList = [];
        $errorListFilter = $filter->validate($validator);

        if (!empty($errorListFilter)
        && array_key_exists('type', $errorListFilter)) {
            $errorList["{$errorPrefix}_filter_type"] = $errorListFilter['type'];
        }

        if (!empty($errorListFilter)
        && array_key_exists('value', $errorListFilter)) {
            $errorList["{$errorPrefix}_filter_value"] = $errorListFilter['value'];
        }

        return $errorList;
    }
}
