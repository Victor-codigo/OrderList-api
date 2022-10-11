<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;

final class UserRepository extends RepositoryBase implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function save(User $user): void
    {
        try {
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromEmail($user->getEmail()->getValue(), $e->getCode());
        } catch (ConnectionException $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    public function remove(User $user): void
    {
        try {
            $this->objectManager->remove($user);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromEmail($user->getEmail()->getValue(), $e->getCode());
        } catch (ConnectionException $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }
}
