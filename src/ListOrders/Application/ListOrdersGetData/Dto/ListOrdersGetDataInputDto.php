<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $listOrdersId;
    public readonly bool $orderAsc;

    public readonly Filter|null $filterSection;
    public readonly Filter|null $filterText;

    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;

    public function __construct(UserShared $userShared, string|null $groupId, array|null $listOrdersIds, string|null $filterValue, bool $orderAsc, string|null $filterSection, string|null $filterText, int|null $page, int|null $pageItems)
    {
        $this->userSession = $userShared;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->listOrdersId = array_map(
            fn (string $listOrderId) => ValueObjectFactory::createIdentifier($listOrderId),
            $listOrdersIds ?? []
        );
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
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorListGroupId = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);

        $errorListListOrdersIdsAndListOrdersNameStartsWith = $this->validateListOrdersIdAndListOrdersIdNameStartsWith($validator);

        return array_merge(
            $errorListGroupId,
            $errorListListOrdersIdsAndListOrdersNameStartsWith
        );
    }

    private function validateListOrdersIdAndListOrdersIdNameStartsWith(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray(['group_id' => $this->groupId]);
        $errorListListOrdersId = $validator->validateValueObjectArray($this->listOrdersId);
        $errorListPage = $validator->validateValueObject($this->page);
        $errorListPageItems = $validator->validateValueObject($this->pageItems);
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

        if (!empty($errorListListOrdersId)) {
            $errorList['list_orders_id'] = $errorListListOrdersId;
        }

        if (!empty($errorListPage)) {
            $errorList['page'] = $errorListPage;
        }

        if (!empty($errorListPageItems)) {
            $errorList['page_items'] = $errorListPageItems;
        }

        if (!empty($errorListFilterSection)) {
            $errorList = array_merge($errorList, $errorListFilterSection);
        }

        if (!empty($errorListFilterTest)) {
            $errorList = array_merge($errorList, $errorListFilterTest);
        }

        return $errorList;
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
