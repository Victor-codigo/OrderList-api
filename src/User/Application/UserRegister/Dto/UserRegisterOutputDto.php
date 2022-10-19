<?php

declare(strict_types=1);

namespace User\Application\UserRegister\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class UserRegisterOutputDto
{
    public readonly Identifier $id;

    public function __construct(Identifier $id)
    {
        $this->id = $id;
    }
}
