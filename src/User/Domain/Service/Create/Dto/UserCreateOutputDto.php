<?php

declare(strict_types=1);

namespace User\Domain\Service\Create\Dto;

class UserCreateOutputDto
{
    public readonly string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function create(string $id): self
    {
        return new self($id);
    }
}
