<?php

declare(strict_types=1);

namespace User\Application\UserRegister\Dto;

final class ProfileCreateInputDto
{
    public readonly ?string $image;

    private function __construct(?string $image = null)
    {
        $this->image = $image;
    }

    public static function create(?string $image = null): self
    {
        return new self($image);
    }
}
