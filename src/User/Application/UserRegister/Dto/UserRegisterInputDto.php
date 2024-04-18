<?php

declare(strict_types=1);

namespace User\Application\UserRegister\Dto;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

final class UserRegisterInputDto implements ServiceInputDtoInterface
{
    public readonly ?Email $email;
    public readonly ?Password $password;
    public readonly ?NameWithSpaces $name;
    public readonly ?Roles $roles;
    public readonly ?ProfileCreateInputDto $profile;
    public readonly ?Url $userRegisterEmailConfirmationUrl;

    /**
     * @param Rol[]|null $roles
     */
    private function __construct(
        ?string $email,
        ?string $password,
        ?string $name,
        ?array $roles,
        ?ProfileCreateInputDto $profile,
        ?string $userRegisterEmailConfirmationUrl
    ) {
        $this->email = ValueObjectFactory::createEmail($email);
        $this->password = ValueObjectFactory::createPassword($password);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->roles = ValueObjectFactory::createRoles($roles);
        $this->profile = $profile;

        $this->userRegisterEmailConfirmationUrl = ValueObjectFactory::createUrl($userRegisterEmailConfirmationUrl);
    }

    public static function create(
        ?string $email,
        ?string $password,
        ?string $name,
        ?array $roles,
        ?string $userRegisterEmailConfirmationUrl
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
