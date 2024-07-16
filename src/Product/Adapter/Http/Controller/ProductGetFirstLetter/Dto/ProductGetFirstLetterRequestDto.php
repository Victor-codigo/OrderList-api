<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductGetFirstLetter\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductGetFirstLetterRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
    }
}
