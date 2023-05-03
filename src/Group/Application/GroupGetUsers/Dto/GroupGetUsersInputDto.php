<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers\Dto;

use Common\Domain\Config\AppConfig;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class GroupGetUsersInputDto implements ServiceInputDtoInterface
{
    private const LIMIT_USERS_MAX = AppConfig::ENDPOINT_GROUP_GET_USERS_MAX_USERS;

    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;

    public function __construct(UserShared $userSession, string|null $groupId, int|null $page, int|null $pageItems)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'page' => $this->page,
            'page_items' => $this->pageItems,
        ]);
    }
}
