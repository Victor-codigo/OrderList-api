<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupGetDataRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int GROUPS_NUM_MAX = AppConfig::ENDPOINT_GROUP_GET_DATA_MAX_GROUPS;

    /**
     * @var string[]|null
     */
    public readonly ?array $groupsId;

    public function __construct(Request $request)
    {
        $this->groupsId = $this->validateCsvOverflow($request->attributes->get('groups_id'), self::GROUPS_NUM_MAX);
    }
}
