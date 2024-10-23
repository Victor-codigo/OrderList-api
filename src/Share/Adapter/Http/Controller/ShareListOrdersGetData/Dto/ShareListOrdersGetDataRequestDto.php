<?php

declare(strict_types=1);

namespace Share\Adapter\Http\Controller\ShareListOrdersGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class ShareListOrdersGetDataRequestDto implements RequestDtoInterface
{
    public ?string $sharedListOrdersId;

    public function __construct(Request $request)
    {
        $this->sharedListOrdersId = $request->query->get('shared_list_orders_id');
    }
}
