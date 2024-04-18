<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\User\USER_ROLES;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;

class UserRepository extends RepositoryBase implements UserRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, User::class, $paginator);
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
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
     * @param User[] $users
     *
     * @throws DBConnectionException
     */
    public function remove(array $users): void
    {
        try {
            foreach ($users as $user) {
                $this->objectManager->remove($user);
            }

            $this->objectManager->flush();
        } catch (Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBNotFoundException
     */
    public function findUserByIdOrFail(Identifier $id): User
    {
        return $this->findUserByIdCacheOrFail($id, true);
    }

    /**
     * WARNING! this method will override User entity with data base info, any changes in user, will be lost.
     *
     * @throws DBNotFoundException
     */
    public function findUserByIdNoCacheOrFail(Identifier $id): User
    {
        return $this->findUserByIdCacheOrFail($id, false);
    }

    /**
     * WARNING! this method will override User entity with data base info, any changes in user, will be lost.
     *
     * @throws DBNotFoundException
     */
    private function findUserByIdCacheOrFail(Identifier $id, bool $cache = true): User
    {
        $query = $this->entityManager
            ->createQuery('SELECT u FROM '.User::class.' u WHERE u.id=:id')
            ->setParameter('id', $id);

        if (!$cache) {
            $query->setHint(Query::HINT_REFRESH, true);
        }

        $user = $query->getOneOrNullResult();

        if (null === $user) {
            throw DBNotFoundException::fromMessage(sprintf('User with id:"%s". Not found', $id->getValue()));
        }

        return $user;
    }

    public function findUserByEmailOrFail(Email $email): User
    {
        $user = $this->findOneBy(['email' => $email]);

        if (null === $user) {
            throw DBNotFoundException::fromMessage(sprintf('User with email:"%s". Not found', $email->getValue()));
        }

        return $user;
    }

    /**
     * @param Identifier[] $id
     *
     * @return Users[]
     *
     * @throws DBNotFoundException
     */
    public function findUsersByIdOrFail(array $id): array
    {
        $users = $this->findBy(['id' => $id]);

        if (empty($users)) {
            throw DBNotFoundException::fromMessage('Users not found');
        }

        return $users;
    }

    /**
     * @param NameWithSpaces[] $usersName
     *
     * @return Users[]
     *
     * @throws DBNotFoundException
     */
    public function findUsersByNameOrFail(array $usersName): array
    {
        $users = $this->findBy(['name' => $usersName]);

        if (empty($users)) {
            throw DBNotFoundException::fromMessage('Users not found');
        }

        return $users;
    }

    /**
     * @throws DBNotFoundException
     */
    public function findUsersTimeActivationExpiredOrFail(int $activationTime): PaginatorInterface
    {
        $userEntity = User::class;
        $dql = <<<DQL
            SELECT user
            FROM {$userEntity} user
            WHERE JSON_CONTAINS(user.roles, :rol) = 1
                AND user.createdOn + :activationTime < CURRENT_TIMESTAMP()
        DQL;

        $query = $this->entityManager->createQuery($dql)
            ->setParameters([
                'rol' => '"'.USER_ROLES::NOT_ACTIVE->value.'"',
                'activationTime' => $activationTime,
            ]);
        $paginator = $this->paginator->createPaginator($query);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('No users found');
        }

        return $paginator;
    }
}
