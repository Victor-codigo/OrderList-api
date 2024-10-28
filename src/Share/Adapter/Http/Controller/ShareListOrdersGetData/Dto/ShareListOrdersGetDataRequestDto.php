<?php

declare(strict_types=1);

namespace Share\Adapter\Http\Controller\ShareListOrdersGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class ShareListOrdersGetDataRequestDto implements RequestDtoInterface
{
    public ?string $sharedListOrdersId;
    public ?int $page;
    public ?int $pageItems;

    public function __construct(Request $request)
    {
        $this->sharedListOrdersId = $request->query->get('shared_list_orders_id');
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
    }
}
