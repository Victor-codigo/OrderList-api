<?php

declare(strict_types=1);

namespace User\Application\UserRegister\Dto;

final class ProfileCreateInputDto
{
    public readonly string|null $image;

    private function __construct(string|null $image = null)
    {
        $this->image = $image;
    }

    public static function create(string|null $image = null): self
    {
        return new self($image);
    }
}
