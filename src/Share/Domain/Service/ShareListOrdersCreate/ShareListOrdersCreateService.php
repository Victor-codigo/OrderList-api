<?php

declare(strict_types=1);

namespace Share\Domain\Service\ShareListOrdersCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareListOrdersCreate\Dto\ShareListOrderCreateDto;
use User\Domain\Port\Repository\UserRepositoryInterface;

class ShareListOrdersCreateService
{
    public function __construct(
        private ShareRepositoryInterface $shareRepository,
        private ListOrdersRepositoryInterface $listOrdersRepository,
        private UserRepositoryInterface $userRepository,
        private int $sharedExpirationTime,
    ) {
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     * @throws DBNotFoundException
     */
    public function __invoke(ShareListOrderCreateDto $input): Share
    {
        $share = $this->createShare($input->listOrdersId, $input->userId, $this->sharedExpirationTime);
        $this->shareRepository->save($share);

        return $share;
    }

    /**
     * @throws DBNotFoundException
     */
    private function createShare(Identifier $listOrderId, Identifier $userId, int $sharedExpirationTime): Share
    {
        $user = $this->userRepository->findUserByIdOrFail($userId);
        $listOrders = $this->getListOrders($listOrderId);

        $shareId = ValueObjectFactory::createIdentifier($this->shareRepository->generateId());
        $expire = (new \DateTime())->setTimestamp(time() + $sharedExpirationTime);

        return new Share(
            $shareId,
            $listOrders,
            $user,
            $expire
        );
    }

    /**
     * @throws DBNotFoundException
     */
    private function getListOrders(Identifier $listOrdersId): ListOrders
    {
        $listOrdersPaginator = $this->listOrdersRepository->findListOrderByIdOrFail([$listOrdersId]);
        $listOrdersPaginator->setPagination(1, 1);

        foreach ($listOrdersPaginator->getIterator() as $listOrders) {
            return $listOrders;
        }
    }
}
