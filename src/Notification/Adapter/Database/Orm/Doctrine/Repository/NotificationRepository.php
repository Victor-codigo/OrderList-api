<?php

declare(strict_types=1);

namespace Notification\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;

class NotificationRepository extends RepositoryBase implements NotificationRepositoryInterface
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $managerRegistry, PaginatorInterface $paginator)
    {
        parent::__construct($managerRegistry, Notification::class);

        $this->paginator = $paginator;
    }

    /**
     * @param Notification[] $notifications
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $notifications): void
    {
        try {
            foreach ($notifications as $notification) {
                $this->objectManager->persist($notification);
            }

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($notification->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Notification[] $notifications
     *
     * @throws DBConnectionException
     */
    public function remove(array $notifications): void
    {
        try {
            foreach ($notifications as $notification) {
                $this->objectManager->remove($notification);
            }

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Identifier[] $notificationsId
     *
     * @throws DBNotFoundException
     */
    public function getNotificationsByIdOrFail(array $notificationsId): PaginatorInterface
    {
        if (empty($notificationsId)) {
            throw DBNotFoundException::fromMessage('No notifications found');
        }

        $placeholders = [];
        foreach ($notificationsId as $key => $notificationId) {
            $placeholders["notificationId{$key}"] = $notificationId->getValue();
        }

        $placeholdersNames = implode(', :', array_keys($placeholders));
        $notificationEntity = Notification::class;
        $dql = <<<DQL
            SELECT notification
            FROM {$notificationEntity} notification
            WHERE notification.id IN (:{$placeholdersNames})
        DQL;

        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameters($placeholders);

        $paginator = $this->paginator->createPaginator($query);

        if (0 === count($paginator)) {
            throw DBNotFoundException::fromMessage('No notifications found');
        }

        return $paginator;
    }

    /**
     * @param Identifier[] $notificationsId
     *
     * @throws DBNotFoundException
     */
    public function getNotificationByUserIdOrFail(Identifier $userId): PaginatorInterface
    {
        $notificationEntity = Notification::class;
        $dql = <<<DQL
            SELECT notification
            FROM {$notificationEntity} notification
            WHERE notification.userId = :userId
        DQL;

        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameter('userId', $userId);

        $paginator = $this->paginator->createPaginator($query);

        if (0 === count($paginator)) {
            throw DBNotFoundException::fromMessage('No notifications found');
        }

        return $paginator;
    }
}
