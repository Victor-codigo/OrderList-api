<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Security;

use Common\Domain\Security\UserShared;

interface UserSharedInterface
{
    public function getUser(): UserShared;

    public function setUser(UserShared $user): self;
}
