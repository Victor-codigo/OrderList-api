<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersCreate;

use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersCreate\Dto\ListOrdersCreateDto;

class ListOrdersCreateService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository
    ) {
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersCreateDto $input): ListOrders
    {
        $listOrders = $this->createListOrders($input->groupId, $input->userId, $input->name, $input->description, $input->dateToBuy);

        $this->listOrdersRepository->save([$listOrders]);

        return $listOrders;
    }

    private function createListOrders(Identifier $groupId, Identifier $userId, NameWithSpaces $name, Description $description, DateNowToFuture $dateToBuy): ListOrders
    {
        $listOrdersId = $this->listOrdersRepository->generateId();

        return new ListOrders(
            ValueObjectFactory::createIdentifier($listOrdersId),
            $groupId,
            $userId,
            $name,
            $description,
            $dateToBuy
        );
    }
}
