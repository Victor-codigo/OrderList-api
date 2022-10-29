<?php

declare(strict_types=1);

namespace User\Application\UserPasswordChange\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class UserPasswordChangeInputDto implements ServiceInputDtoInterface
{
    public readonly Identifier|null $id;
    public readonly Password|null $passwordOld;
    public readonly Password|null $passwordNew;
    public readonly Password|null $passwordNewRepeat;

    public function __construct(string|null $id, string|null $passwordOld, string|null $passordNew, string|null $passwordNewRepeat)
    {
        $this->id = ValueObjectFactory::createIdentifier($id);
        $this->passwordOld = ValueObjectFactory::createPassword($passwordOld);
        $this->passwordNew = ValueObjectFactory::createPassword($passordNew);
        $this->passwordNewRepeat = ValueObjectFactory::createPassword($passwordNewRepeat);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'id' => $this->id,
            'passwordOld' => $this->passwordOld,
            'passwordNew' => $this->passwordNew,
            'passwordNewRepeat' => $this->passwordNewRepeat,
        ]);
    }
}
