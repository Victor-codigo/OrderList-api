<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersGetDataRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int LIST_ORDERS_IDS_MAX = AppConfig::ENDPOINT_LIST_ORDERS_GET_DATA_MAX;

    public readonly string|null $groupId;
    /**
     * @var string[]|null
     */
    public readonly array|null $listOrdersIds;
    public readonly bool $orderAsc;

    public readonly string|null $filterValue;
    public readonly string|null $filterSection;
    public readonly string|null $filterText;

    public readonly int|null $page;
    public readonly int|null $pageItems;

    public function __construct(Request $request)
    {
        $this->listOrdersIds = $this->validateCsvOverflow($request->query->get('list_orders_id'), self::LIST_ORDERS_IDS_MAX);
        $this->groupId = $request->query->get('group_id');
        $this->orderAsc = $request->query->getBoolean('order_asc');
        $this->filterValue = $request->query->get('filter_value');
        $this->filterSection = $request->query->get('filter_section');
        $this->filterText = $request->query->get('filter_text');
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
    }
}
