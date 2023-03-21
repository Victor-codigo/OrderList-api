<?php

declare(strict_types=1);

namespace User\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Repository\RepositoryInterface;
use User\Domain\Model\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(User $user): void;

    /**
     * @throws DBNotFoundException
     */
    public function findUserByIdOrFail(Identifier $id): User;

    /**
     * @throws DBNotFoundException
     */
    public function findUserByEmailOrFail(Email $email): User;

    /**
     * WARNING! this method will override User entity with data base info, any changes in user, will be lost.
     *
     * @throws DBNotFoundException
     */
    public function findUserByIdNoCacheOrFail(Identifier $id): User;

    /**
     * @param Identifier[] $id
     *
     * @return Users[]
     *
     * @throws DBNotFoundException
     */
    public function findUsersByIdOrFail(array $id): array;
}
