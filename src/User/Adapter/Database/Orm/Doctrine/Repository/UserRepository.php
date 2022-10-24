<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;

class UserRepository extends RepositoryBase implements UserRepositoryInterface
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
        } catch (Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBNotFoundException
     */
    public function findUserByIdOrFail(Identifier $id): object
    {
        $user = $this->findOneBy(['id' => $id]);

        if (null === $user) {
            throw DBNotFoundException::fromMessage(sprintf('User with id:"%s". Not found: ', $id->getValue()));
        }

        return $user;
    }
}
