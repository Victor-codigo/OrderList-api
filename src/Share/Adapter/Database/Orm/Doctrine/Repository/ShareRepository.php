<?php

declare(strict_types=1);

namespace Share\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\EntityIdentityCollisionException;
use Doctrine\Persistence\ManagerRegistry;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;

/**
 * @phpstan-extends RepositoryBase<Share>
 */
class ShareRepository extends RepositoryBase implements ShareRepositoryInterface
{
    /**
     * @param PaginatorInterface<int, object> $paginator
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator,
    ) {
        parent::__construct($managerRegistry, Share::class, $paginator);
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Share $share): void
    {
        try {
            $this->objectManager->persist($share);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException|EntityIdentityCollisionException $e) {
            throw DBUniqueConstraintException::fromId($share->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Share[] $shares
     *
     * @throws DBConnectionException
     */
    public function remove(array $shares): void
    {
        try {
            foreach ($shares as $share) {
                $this->objectManager->remove($share);
            }

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }
}
