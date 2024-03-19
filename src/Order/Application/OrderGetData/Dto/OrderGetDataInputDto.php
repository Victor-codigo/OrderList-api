<?php

declare(strict_types=1);

namespace Order\Application\OrderGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;

class OrderGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly IdentifierNullable $listOrdersId;
    /**
     * @var Identifier[]
     */
    public readonly array $ordersId;

    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;
    public readonly bool $orderAsc;
    public readonly ?Filter $filterSection;
    public readonly ?Filter $filterText;

    /**
     * @param string[]|null $ordersId
     */
    public function __construct(UserShared $userSession, ?string $groupId, ?string $listOrdersId, ?array $ordersId, ?int $page, ?int $pageItems, bool $orderAsc, ?string $filterSection, ?string $filterText, ?string $filterValue)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->listOrdersId = ValueObjectFactory::createIdentifierNullable($listOrdersId);
        $this->ordersId = array_map(
            fn (string $orderId) => ValueObjectFactory::createIdentifier($orderId),
            $ordersId ?? []
        );
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
        $this->orderAsc = $orderAsc;
        $this->filterSection = null === $filterSection ? null : ValueObjectFactory::createFilter(
            'section_filter',
            ValueObjectFactory::createFilterSection(FILTER_SECTION::tryFrom($filterSection)),
            ValueObjectFactory::createNameWithSpaces($filterValue)
        );
        $this->filterText = null === $filterText ? null : ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::tryFrom($filterText)),
            ValueObjectFactory::createNameWithSpaces($filterValue)
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'list_orders_id' => $this->listOrdersId,
            'page' => $this->page,
            'page_items' => $this->pageItems,
        ]);

        $errorListOrders = $this->validateOrdersId($validator);

        $errorListFilterSection = null === $this->filterSection
            ? []
            : $this->validateFilter($validator, $this->filterSection, 'section');
        $errorListFilterTest = null === $this->filterText
            ? []
            : $this->validateFilter($validator, $this->filterText, 'text');

        if (null !== $this->filterSection && null === $this->filterText
        || null === $this->filterSection && null !== $this->filterText) {
            $errorList['filter_section_and_text_not_empty'] = [VALIDATION_ERRORS::NOT_NULL];
        }

        return array_merge($errorList, $errorListOrders, $errorListFilterSection, $errorListFilterTest);
    }

    private function validateOrdersId(ValidationInterface $validator): array
    {
        $errorListOrdersId = $validator->validateValueObjectArray($this->ordersId);

        if (!empty($errorListOrdersId)) {
            $errorListOrdersId = ['orders_id' => $errorListOrdersId];
        }

        return $errorListOrdersId;
    }

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
