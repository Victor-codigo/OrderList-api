<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRemember\Dto;

use Override;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class UserPasswordRememberInputDto implements ServiceInputDtoInterface
{
    public readonly Email|null $email;
    public readonly Url|null $passwordRememberUrl;

    public function __construct(string|null $email, string|null $passwordRememberUrl)
    {
        $this->email = ValueObjectFactory::createEmail($email);
        $this->passwordRememberUrl = ValueObjectFactory::createUrl($passwordRememberUrl);
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'email' => $this->email,
            'passwordRememberUrl' => $this->passwordRememberUrl,
        ]);
    }
}
