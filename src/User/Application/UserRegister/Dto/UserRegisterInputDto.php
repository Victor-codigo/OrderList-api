<?php

declare(strict_types=1);

namespace User\Application\UserRegister\Dto;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

final class UserRegisterInputDto implements ServiceInputDtoInterface
{
    public readonly Email|null $email;
    public readonly Password|null $password;
    public readonly Name|null $name;
    public readonly Roles|null $roles;
    public readonly ProfileCreateInputDto|null $profile;
    public readonly Url|null $userRegisterEmailConfirmationUrl;

    /**
     * @param Rol[]|null $roles
     */
    private function __construct(
        string|null $email,
        string|null $password,
        string|null $name,
        array|null $roles,
        ProfileCreateInputDto|null $profile,
        string|null $userRegisterEmailConfirmationUrl
    ) {
        $this->email = ValueObjectFactory::createEmail($email);
        $this->password = ValueObjectFactory::createPassword($password);
        $this->name = ValueObjectFactory::createName($name);
        $this->roles = ValueObjectFactory::createRoles($roles);
        $this->profile = $profile;

        $this->userRegisterEmailConfirmationUrl = ValueObjectFactory::createUrl($userRegisterEmailConfirmationUrl);
    }

    public static function create(
        string|null $email,
        string|null $password,
        string|null $name,
        array|null $roles,
        string|null $userRegisterEmailConfirmationUrl
    ): self {
        $profile = ProfileCreateInputDto::create(null);

        return new self(
            $email,
            $password,
            $name,
            $roles,
            $profile,
            $userRegisterEmailConfirmationUrl
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'email' => $this->email,
            'password' => $this->password,
            'name' => $this->name,
            'roles' => $this->roles,
            'email_confirmation_url' => $this->userRegisterEmailConfirmationUrl,
        ]);
    }
}
