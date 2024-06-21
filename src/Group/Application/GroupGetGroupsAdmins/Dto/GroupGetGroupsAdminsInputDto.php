<?php

declare(strict_types=1);

namespace Group\Application\GroupGetGroupsAdmins\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class GroupGetGroupsAdminsInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $groupsId;
    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;

    /**
     * @param string[]|null $groupsId
     */
    public function __construct(UserShared $userSession, ?array $groupsId, ?int $page, ?int $pageItems)
    {
        $this->userSession = $userSession;
        $this->groupsId = array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsId ?? []
        );
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorListGroupsIdEmpty = $validator
            ->setValue($this->groupsId)
            ->notBlank()
            ->validate();

        $errorListGroupsId = $validator->validateValueObjectArray($this->groupsId);
        $errorList = $validator->validateValueObjectArray([
            'page' => $this->page,
            'page_items' => $this->pageItems,
        ]);

        if (!empty($errorListGroupsIdEmpty)) {
            $errorListGroupsIdEmpty = ['groups_id_empty' => $errorListGroupsIdEmpty];
        }

        if (!empty($errorListGroupsId)) {
            $errorListGroupsId = ['groups_id' => $errorListGroupsId];
        }

        return [...$errorList, ...$errorListGroupsIdEmpty, ...$errorListGroupsId];
    }
}
