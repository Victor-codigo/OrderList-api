<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class OrderGetDataRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int ORDERS_NUM_MAX = AppConfig::ENDPOINT_ORDER_GET_MAX;

    public readonly ?string $groupId;
    public readonly ?string $listOrdersId;
    /**
     * @var string[]|null
     */
    public readonly ?array $ordersId;

    public readonly ?int $page;
    public readonly ?int $pageItems;
    public readonly ?bool $orderAsc;
    public readonly ?string $filterSection;
    public readonly ?string $filterText;
    public readonly ?string $filterValue;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
        $this->listOrdersId = $request->query->get('list_orders_id');
        $this->ordersId = $this->validateCsvOverflow($request->query->get('orders_id'), self::ORDERS_NUM_MAX);

        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
        $this->orderAsc = $request->query->getBoolean('order_asc', true);

        $this->filterSection = $request->query->get('filter_section');
        $this->filterText = $request->query->get('filter_text');
        $this->filterValue = $request->query->get('filter_value');
    }
}
