<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\GetUsers\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GetUsersRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int USERS_NUM_MAX = AppConfig::GROUP_USERS_MAX;

    /**
     * @var string[]
     */
    public readonly ?array $usersId;

    public function __construct(Request $request)
    {
        $requestUsersId = $request->attributes->get('users_id');
        $this->usersId = $this->validateCsvOverflow($requestUsersId, self::USERS_NUM_MAX);
    }
}
