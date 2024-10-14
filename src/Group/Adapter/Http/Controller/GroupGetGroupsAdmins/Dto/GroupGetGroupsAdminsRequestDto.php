<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetGroupsAdmins\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupGetGroupsAdminsRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int GROUPS_ID_MAX = AppConfig::ENDPOINT_GROUP_GET_GROUPS_ADMINS_MAX;

    /**
     * @var string[]|null
     */
    public readonly ?array $groupsId;
    public readonly ?int $page;
    public readonly ?int $pageItems;

    public function __construct(Request $request)
    {
        $this->groupsId = $this->validateCsvOverflow(
            $request->attributes->get('groups_id'),
            self::GROUPS_ID_MAX
        );
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
    }
}
