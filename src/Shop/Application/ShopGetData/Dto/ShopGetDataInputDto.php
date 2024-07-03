<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetData\Dto;

use Override;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;

class ShopGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $shopsId;
    /**
     * @var Identifier[]
     */
    public readonly array $productsId;
    public readonly NameWithSpaces|null $shopName;
    public readonly Filter|null $shopNameFilter;
    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;
    public readonly bool $orderAsc;

    public function __construct(
        string|null $groupId,
        array|null $shopsId,
        array|null $productsId,
        string|null $shopNameFilterType,
        string|null $shopNameFilterValue,
        string|null $shopName,
        bool|null $orderAsc,
        int|null $page,
        int|null $pageItems
    ) {
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->shopName = ValueObjectFactory::createNameWithSpaces($shopName);
        $this->shopsId = array_map(
            fn (string $shopId): Identifier => ValueObjectFactory::createIdentifier($shopId),
            $shopsId ?? []
        );
        $this->productsId = array_map(
            fn (string $productId): Identifier => ValueObjectFactory::createIdentifier($productId),
            $productsId ?? []
        );

        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
        $this->orderAsc = $orderAsc ?? true;
        $this->shopNameFilter = ValueObjectFactory::createFilter(
            'shop_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::tryFrom($shopNameFilterType ?? '')),
            ValueObjectFactory::createNameWithSpaces($shopNameFilterValue)
        );
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray(['group_id' => $this->groupId]);
        $errorListShopsId = $validator->validateValueObjectArray($this->shopsId);
        $errorListProductsId = $validator->validateValueObjectArray($this->productsId);
        $errorListPage = $validator->validateValueObject($this->page);
        $errorListPageItems = $validator->validateValueObject($this->pageItems);
        $errorListShopNameFilter = $this->validateFilter($validator);

        if (!$this->shopName->isNull()) {
            $errorListShopName = $validator->validateValueObject($this->shopName);
        }

        if (!empty($errorListShopName)) {
            $errorList['shop_name'] = $errorListShopName;
        }

        if (!empty($errorListShopsId)) {
            $errorList['shops_id'] = $errorListShopsId;
        }

        if (!empty($errorListProductsId)) {
            $errorList['products_id'] = $errorListProductsId;
        }

        if (!empty($errorListPage)) {
            $errorList['page'] = $errorListPage;
        }
        if (!empty($errorListPageItems)) {
            $errorList['page_items'] = $errorListPageItems;
        }

        if (!empty($errorListShopNameFilter)) {
            $errorList = array_merge($errorList, $errorListShopNameFilter);
        }

        return $errorList;
    }

    private function validateFilter(ValidationInterface $validator): array
    {
        if ($this->shopNameFilter->getFilter()->isNull()
        && $this->shopNameFilter->isNull()) {
            return [];
        }

        $errorList = [];
        $errorListShopNameFilter = $this->shopNameFilter->validate($validator);

        if (!empty($errorListShopNameFilter)
        && array_key_exists('type', $errorListShopNameFilter)) {
            $errorList['shop_filter_type'] = $errorListShopNameFilter['type'];
        }

        if (!empty($errorListShopNameFilter)
        && array_key_exists('value', $errorListShopNameFilter)) {
            $errorList['shop_filter_value'] = $errorListShopNameFilter['value'];
        }

        return $errorList;
    }
}
