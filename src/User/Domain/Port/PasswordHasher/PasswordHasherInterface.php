<?php

declare(strict_types=1);

namespace User\Domain\Port\PasswordHasher;

use Common\Domain\Model\ValueObject\String\Password;

interface PasswordHasherInterface
{
    public function passwordHash(string $plainPassword): Password;

    public function passwordIsValid(string $plainPassword): bool;

    public function passwordNeedsRehash(Password $passowrd): bool;
}
