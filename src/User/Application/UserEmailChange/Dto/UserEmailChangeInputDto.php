<?php

declare(strict_types=1);

namespace User\Application\UserEmailChange\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class UserEmailChangeInputDto implements ServiceInputDtoInterface
{
    public readonly Email $userEmail;
    public readonly Email $email;
    public readonly Password $password;

    public function __construct(string $userEmail, string|null $email, string|null $password)
    {
        $this->userEmail = ValueObjectFactory::createEmail($userEmail);
        $this->email = ValueObjectFactory::createEmail($email);
        $this->password = ValueObjectFactory::createPassword($password);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'email' => $this->email,
            'password' => $this->password,
        ]);
    }
}
