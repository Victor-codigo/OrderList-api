<?php

declare(strict_types=1);

namespace Share\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
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

    /**
     * @param Identifier[] $sharedId
     *
     * @return PaginatorInterface<int, Share>
     *
     * @throws DBNotFoundException
     */
    public function findSharedRecursesByIdOrFail(array $sharedId): PaginatorInterface
    {
        $shareEntity = Share::class;
        $dql = <<<DQL
            SELECT share
            FROM {$shareEntity} share
            WHERE share.id IN (:sharedId)
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'sharedId' => $sharedId,
        ]);
    }

    /**
     * @return PaginatorInterface<int, Share>
     *
     * @throws DBNotFoundException
     */
    public function findSharedRecursesExpiredOrFail(): PaginatorInterface
    {
        $shareEntity = Share::class;
        $dateNow = (new \DateTime())->format('Y-m-d H:i:00');
        $dql = <<<DQL
            SELECT share
            FROM {$shareEntity} share
            WHERE share.expire < '{$dateNow}'
        DQL;

        return $this->dqlPaginationOrFail($dql);
    }
}
