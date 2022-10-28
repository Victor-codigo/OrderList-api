<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRemember\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class UserPasswordRememberInputDto implements ServiceInputDtoInterface
{
    public readonly Email|null $email;

    public function __construct(string|null $email)
    {
        $this->email = ValueObjectFactory::createEmail($email);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray(['email' => $this->email]);
    }
}
