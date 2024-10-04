<?php

declare(strict_types=1);

namespace Share\Adapter\Http\Controller\ShareListOrdersCreate\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class ShareListOrdersCreateRequestDto implements RequestDtoInterface
{
    public ?string $listOrdersId;

    public function __construct(Request $request)
    {
        $this->listOrdersId = $request->request->get('list_orders_id');
    }
}
