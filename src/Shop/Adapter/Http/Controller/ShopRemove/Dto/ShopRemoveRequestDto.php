<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ShopRemoveRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int SHOPS_MAX = AppConfig::ENDPOINT_SHOP_REMOVE_MAX;

    public readonly ?array $shopsId;
    public readonly ?string $groupId;

    public function __construct(Request $request)
    {
        $this->shopsId = $this->validateArrayOverflow($request->request->all('shops_id'), self::SHOPS_MAX);
        $this->groupId = $request->request->get('group_id');
    }
}
