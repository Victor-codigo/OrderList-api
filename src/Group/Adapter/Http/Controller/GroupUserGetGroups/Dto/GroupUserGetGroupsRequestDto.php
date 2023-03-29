<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserGetGroups\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupUserGetGroupsRequestDto implements RequestDtoInterface
{
    public readonly int $page;
    public readonly int $pageItems;

    public function __construct(Request $request)
    {
        $this->page = $request->query->getInt('page', 1);
        $this->pageItems = $request->query->getInt('page_items', 1);
    }
}
