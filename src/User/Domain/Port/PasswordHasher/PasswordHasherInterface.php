<?php

declare(strict_types=1);

namespace User\Domain\Port\PasswordHasher;

interface PasswordHasherInterface
{
    public function passwordHash(string $plainPassword): void;

    public function passwordIsValid(string $plainPassword): bool;

    public function passwordNeedsRehash(): bool;
}
