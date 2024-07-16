<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopGetFirstLetter\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class ShopGetFirstLetterRequestDto implements RequestDtoInterface
{
    public ?string $groupId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
    }
}
