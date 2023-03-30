<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserGetByName\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class UserGetByNameRequestDto implements RequestDtoInterface
{
    private const USERS_NUM_MAX = 50;

    public readonly array|null $usersName;

    public function __construct(Request $request)
    {
        $usersName = $request->attributes->get('users_name');
        $this->usersName = $this->removeUsersOverflow($usersName);
    }

    private function removeUsersOverflow(string|null $usersName): array|null
    {
        if (null === $usersName) {
            return null;
        }

        $usersIdValid = explode(',', $usersName, self::USERS_NUM_MAX + 1);

        if (count($usersIdValid) > self::USERS_NUM_MAX) {
            $usersIdValid = array_slice($usersIdValid, 0, self::USERS_NUM_MAX);
        }

        return $usersIdValid;
    }
}
