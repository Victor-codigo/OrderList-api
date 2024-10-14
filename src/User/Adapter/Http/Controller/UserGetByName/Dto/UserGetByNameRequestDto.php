<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserGetByName\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Symfony\Component\HttpFoundation\Request;

class UserGetByNameRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;
    private const int USERS_NUM_MAX = 50;

    /**
     * @var string[]|null
     */
    public readonly ?array $usersName;

    public function __construct(Request $request)
    {
        $usersName = $request->attributes->get('users_name');
        $this->usersName = $this->validateCsvOverflow($usersName, self::USERS_NUM_MAX);
    }
}
