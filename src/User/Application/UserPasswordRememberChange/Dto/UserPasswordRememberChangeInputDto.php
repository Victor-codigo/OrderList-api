<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRememberChange\Dto;

use Override;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class UserPasswordRememberChangeInputDto implements ServiceInputDtoInterface
{
    public readonly JwtToken $token;
    public readonly Password $passwordNew;
    public readonly Password $passwordNewRepeat;

    public function __construct(string|null $token, string|null $passwordNew, string|null $passwordNewRepeat)
    {
        $this->token = ValueObjectFactory::createJwtToken($token);
        $this->passwordNew = ValueObjectFactory::createPassword($passwordNew);
        $this->passwordNewRepeat = ValueObjectFactory::createPassword($passwordNewRepeat);
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'token' => $this->token,
            'passwordNew' => $this->passwordNew,
            'passwordNewRepeat' => $this->passwordNewRepeat,
        ]);
    }
}
