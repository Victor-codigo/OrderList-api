<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetData;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetData\Dto\ListOrdersGetDataDto;

class ListOrdersGetDataService
{
    /**
     * @var PaginatorInterface<int, ListOrders>
     */
    private PaginatorInterface $listOrdersPaginator;

    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository,
    ) {
    }

    /**
     * @return array<int, array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * }>
     *
     * @throws DBNotFoundException
     * @throws LogicException
     */
    public function __invoke(ListOrdersGetDataDto $input): array
    {
        $this->listOrdersPaginator = $this->findListOrder($input->groupId, $input->listOrdersId, $input->filterSection, $input->filterText, $input->page, $input->pageItems, $input->orderAsc);

        return $this->getListOrderData($this->listOrdersPaginator);
    }

    public function getPagesTotal(): int
    {
        return $this->listOrdersPaginator->getPagesTotal();
    }

    /**
     * @param Identifier[] $listOrdersId
     *
     * @return PaginatorInterface<int, ListOrders>
     *
     * @throws DBNotFoundException
     * @throws LogicException
     */
    private function findListOrder(Identifier $groupId, array $listOrdersId, ?Filter $filterSection, ?Filter $filterText, PaginatorPage $page, PaginatorPageItems $pageItems, bool $orderAsc): PaginatorInterface
    {
        if (!empty($listOrdersId)) {
            $listOrdersPaginator = $this->listOrdersRepository->findListOrderByIdOrFail($listOrdersId, $groupId);
        } elseif (null !== $filterSection && !$filterSection->isNull()
               || null !== $filterText && !$filterText->isNull()) {
            $listOrdersPaginator = match ($filterSection->getFilter()->getValue()) {
                FILTER_SECTION::LIST_ORDERS => $this->listOrdersRepository->findListOrderByListOrdersNameFilterOrFail($groupId, $filterText, $orderAsc),
                FILTER_SECTION::PRODUCT => $this->listOrdersRepository->findListOrderByProductNameFilterOrFail($groupId, $filterText, $orderAsc),
                FILTER_SECTION::SHOP => $this->listOrdersRepository->findListOrderByShopNameFilterOrFail($groupId, $filterText, $orderAsc),
            };
        } else {
            $listOrdersPaginator = $this->listOrdersRepository->findListOrdersGroup($groupId, $orderAsc);
        }

        $listOrdersPaginator->setPagination($page->getValue(), $pageItems->getValue());

        return $listOrdersPaginator;
    }

    /**
     * @param PaginatorInterface<int, ListOrders> $listsOrders
     *
     * @return array<int, array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * }>
     */
    private function getListOrderData(PaginatorInterface $listsOrders): array
    {
        return array_map(
            fn (ListOrders $listOrders): array => [
                'id' => $listOrders->getId()->getValue(),
                'user_id' => $listOrders->getUserId()->getValue(),
                'group_id' => $listOrders->getGroupId()->getValue(),
                'name' => $listOrders->getName()->getValue(),
                'description' => $listOrders->getDescription()->getValue(),
                'date_to_buy' => $listOrders->getDateToBuy()->getValue()?->format('Y-m-d H:i:s'),
                'created_on' => $listOrders->getCreatedOn()->format('Y-m-d H:i:s'),
            ],
            iterator_to_array($listsOrders)
        );
    }
}
