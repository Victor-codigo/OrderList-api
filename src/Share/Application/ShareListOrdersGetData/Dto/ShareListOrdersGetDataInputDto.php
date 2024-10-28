<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

readonly class ShareListOrdersGetDataInputDto implements ServiceInputDtoInterface
{
    public UserShared $userSession;
    public Identifier $listOrdersId;
    public PaginatorPage $page;
    public PaginatorPageItems $pageItems;

    public function __construct(UserShared $userSession, ?string $listOrdersId, ?int $page, ?int $pageItems)
    {
        $this->userSession = $userSession;
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'shared_list_orders_id' => $this->listOrdersId,
            'page' => $this->page,
            'page_items' => $this->pageItems,
        ]);
    }
}
