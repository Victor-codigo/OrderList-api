<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetOrders\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersGetOrdersInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly Identifier $listOrderId;
    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;

    public function __construct(UserShared $userSession, string|null $groupId, string|null $listOrderId, int|null $page, int|null $pageItems)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->listOrderId = ValueObjectFactory::createIdentifier($listOrderId);
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'list_order_id' => $this->listOrderId,
            'page' => $this->page,
            'page_items' => $this->pageItems,
        ]);
    }
}
