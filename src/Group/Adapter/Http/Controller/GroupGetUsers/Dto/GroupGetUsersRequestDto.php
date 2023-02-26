<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetUsers\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupGetUsersRequestDto implements RequestDtoInterface
{
    private const LIMIT_USERS_MAX = AppConfig::ENDPOINT_GROUP_GET_USERS_MAX_USERS;

    public readonly string|null $groupId;
    public readonly int|null $limit;
    public readonly int|null $offset;

    public function __construct(Request $request)
    {
        $this->groupId = $request->attributes->get('group_id');
        $this->limit = $request->query->getInt('limit', self::LIMIT_USERS_MAX);
        $this->offset = $request->query->getInt('offset');
    }
}
