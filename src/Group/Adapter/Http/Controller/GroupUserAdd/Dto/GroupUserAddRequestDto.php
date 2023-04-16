<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserAdd\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupUserAddRequestDto implements RequestDtoInterface
{
    private const USERS_NUM_MAX = AppConfig::ENDPOINT_GROUP_USER_ADD_MAX_USERS;

    public readonly string|null $groupId;
    public readonly array|null $users;
    public readonly string|null $identifierType;
    public readonly bool|null $admin;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->users = $this->removeUsersOverflow($request->request->get('users'));
        $this->identifierType = $request->request->get('identifier_type');
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
