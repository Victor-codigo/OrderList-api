<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserRoleChange\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupUserRoleChangeRequestDto implements RequestDtoInterface
{
    private const int USERS_NUM_MAX = AppConfig::ENDPOINT_GROUP_USER_ROLE_CHANGE_MAX_USERS;

    public readonly string|null $groupId;
    /**
     * @var string[]|null
     */
    public readonly array|null $usersId;
    public readonly bool|null $admin;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->usersId = $this->removeUsersOverflow($request->request->all('users'));
        $this->admin = $request->request->get('admin');
    }

    /**
     * @param string[]|null $usersId
     */
    private function removeUsersOverflow(array|null $usersId): array|null
    {
        if (null === $usersId) {
            return null;
        }

        if (count($usersId) > self::USERS_NUM_MAX) {
            return array_slice($usersId, 0, self::USERS_NUM_MAX);
        }

        return $usersId;
    }
}
