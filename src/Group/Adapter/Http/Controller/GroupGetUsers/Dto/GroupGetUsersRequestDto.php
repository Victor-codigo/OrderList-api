<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetUsers\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupGetUsersRequestDto implements RequestDtoInterface
{
    private const LIMIT_USERS_MAX = AppConfig::ENDPOINT_GROUP_GET_USERS_MAX_USERS;

    public readonly ?string $groupId;
    public readonly ?int $page;
    public readonly ?int $pageItems;

    public readonly ?string $filterSection;
    public readonly ?string $filterText;
    public readonly ?string $filterValue;
    public readonly bool $orderAsc;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
        $this->page = $request->query->getInt('page', 1);
        $this->pageItems = $request->query->getInt('page_items', self::LIMIT_USERS_MAX);
        $this->filterSection = $request->query->get('filter_section');
        $this->filterText = $request->query->get('filter_text');
        $this->filterValue = $request->query->get('filter_value');
        $this->orderAsc = $request->query->getBoolean('order_asc', true);
    }
}
