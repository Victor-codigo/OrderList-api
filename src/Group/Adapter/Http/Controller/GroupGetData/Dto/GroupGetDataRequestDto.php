<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupGetDataRequestDto implements RequestDtoInterface
{
    private const GROUPS_NUM_MAX = AppConfig::ENDPOINT_GROUP_GET_DATA_MAX_GROUPS;

    public readonly ?array $groupsId;

    public function __construct(Request $request)
    {
        $this->groupsId = $this->removeGroupsOverflow($request->attributes->get('groups_id'));
    }

    private function removeGroupsOverflow(?string $groupsId): ?array
    {
        if (null === $groupsId) {
            return null;
        }

        $groupsIdValid = explode(',', $groupsId, self::GROUPS_NUM_MAX + 1);

        if (count($groupsIdValid) > self::GROUPS_NUM_MAX) {
            $groupsIdValid = array_slice($groupsIdValid, 0, self::GROUPS_NUM_MAX);
        }

        return $groupsIdValid;
    }
}
