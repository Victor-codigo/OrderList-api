<?php

declare(strict_types=1);

namespace User\Domain\Service\Create\Dto;

use Common\Domain\Exception\InvalidArgumentException;

final class UserCreateInputDto
{
    private const EMAIL_MAX_LENGTH = 70;
    private const EMAIL_MIN_LENGTH = 4;
    private const NAME_MAX_LENGTH = 50;
    private const NAME_MIN_LENGTH = 4;
    private const PASSWORD_MAX_LENGTH = 50;
    private const PASSWORD_MIN_LENGTH = 4;

    public readonly string $email;
    public readonly string $password;
    public readonly string $name;
    public readonly ProfileCreateInputDto $profile;

    private function __construct(string|null $email, string|null $password, string|null $name, ProfileCreateInputDto|null $profile)
    {
        $this->emailValidation($email);
        $this->passwordValidation($password);
        $this->nameValidation($name);

        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->profile = $profile;
    }

    public static function create(string|null $email, string|null $password, string|null $name): self
    {
        $profile = ProfileCreateInputDto::create(null);

        return new self($email, $password, $name, $profile);
    }

    public static function createWithProfile(string|null $email, string|null $password, string|null $name, string|null $image): self
    {
        $profile = ProfileCreateInputDto::create($image);

        return new self($email, $password, $name, $profile);
    }

    private function emailValidation(string|null $email): void
    {
        if (null === $email) {
            throw InvalidArgumentException::createFromMessage('Email can\'t be null');
        }

        $length = \strlen($email);

        if ($length < self::EMAIL_MIN_LENGTH || $length > self::EMAIL_MAX_LENGTH) {
            throw InvalidArgumentException::createFromMessage('Email length must be between 4 and {self::EMAIL_LENGTH}');
        }
    }

    private function passwordValidation(string|null $password): void
    {
        if (null === $password) {
            throw InvalidArgumentException::createFromMessage('Password can\'t be null');
        }

        $length = \strlen($password);

        if ($length < self::PASSWORD_MIN_LENGTH || $length > self::PASSWORD_MAX_LENGTH) {
            throw InvalidArgumentException::createFromMessage('Password length must be between 4 and {self::PASSWORD_LENGTH}');
        }
    }

    private function nameValidation(string|null $name): void
    {
        if (null === $name) {
            throw InvalidArgumentException::createFromMessage('Name can\'t be null');
        }

        $length = \strlen($name);

        if ($length < self::NAME_MIN_LENGTH || $length > self::NAME_MAX_LENGTH) {
            throw InvalidArgumentException::createFromMessage('Name length must be between 4 and {self::PASSWORD_LENGTH}');
        }
    }
}
