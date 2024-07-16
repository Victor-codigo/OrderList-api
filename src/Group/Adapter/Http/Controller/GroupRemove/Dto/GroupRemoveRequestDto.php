<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupRemoveRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int GROUPS_REMOVE_MAX = AppConfig::ENDPOINT_GROUP_DELETE_MAX;

    /**
     * @var string[]|null
     */
    public readonly ?array $groupsId;

    public function __construct(Request $request)
    {
        $this->groupsId = $this->validateArrayOverflow($request->request->all('groups_id'), self::GROUPS_REMOVE_MAX);
    }
}
