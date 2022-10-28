<?php

declare(strict_types=1);

namespace User\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Repository\RepositoryInterface;
use User\Domain\Model\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function save(User $user): void;

    /**
     * @throws DBNotFoundException
     */
    public function findUserByIdOrFail(Identifier $id): User;

    /**
     * @throws DBNotFoundException
     */
    public function findUserByEmailOrFail(Email $email): User;
}
