<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRemember\Dto;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class UserPasswordRememberInputDto implements ServiceInputDtoInterface
{
    public readonly ?Email $email;
    public readonly ?Url $passwordRememberUrl;

    public function __construct(?string $email, ?string $passwordRememberUrl)
    {
        $this->email = ValueObjectFactory::createEmail($email);
        $this->passwordRememberUrl = ValueObjectFactory::createUrl($passwordRememberUrl);
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'email' => $this->email,
            'passwordRememberUrl' => $this->passwordRememberUrl,
        ]);
    }
}
