<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\ValidationInterface;

readonly class ShareListOrdersGetDataInputDto implements ServiceInputDtoInterface
{
    public Identifier $listOrdersId;
    public ?Filter $filterText;
    public PaginatorPage $page;
    public PaginatorPageItems $pageItems;

    public function __construct(?string $listOrdersId, ?int $page, ?int $pageItems, ?string $filterText, ?string $filterValue)
    {
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
        $this->filterText = null === $filterText ? null : ValueObjectFactory::createFilter(
            'text_filter',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::tryFrom($filterText)),
            ValueObjectFactory::createNameWithSpaces($filterValue)
        );
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'shared_list_orders_id' => $this->listOrdersId,
            'page' => $this->page,
            'page_items' => $this->pageItems,
        ]);

        $errorListFilterTest = null === $this->filterText
            ? []
            : $this->validateFilter($validator, $this->filterText, 'text');

        if (!empty($errorListFilterTest)) {
            $errorList = array_merge($errorList, $errorListFilterTest);
        }

        return $errorList;
    }

    /**
     * @return array{}|array<string, VALIDATION_ERRORS[]>
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
