<?php

declare(strict_types=1);

namespace User\Application\UserRegisterEmailConfirmation\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class UserEmailConfirmationOutputDto
{
    public readonly Identifier $id;

    public function __construct(Identifier $id)
    {
        $this->id = $id;
    }
}
