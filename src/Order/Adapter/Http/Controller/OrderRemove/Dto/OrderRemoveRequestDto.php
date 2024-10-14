<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class OrderRemoveRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int ORDERS_MAX = AppConfig::ENDPOINT_ORDER_REMOVE_MAX;

    /**
     * @var string[]|null
     */
    public readonly ?array $ordersId;
    public readonly ?string $groupId;

    public function __construct(Request $request)
    {
        $this->ordersId = $this->validateArrayOverflow($request->request->all('orders_id'), self::ORDERS_MAX);
        $this->groupId = $request->request->get('group_id');
    }
}
