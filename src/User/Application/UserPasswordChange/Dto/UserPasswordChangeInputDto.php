<?php

declare(strict_types=1);

namespace User\Application\UserPasswordChange\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class UserPasswordChangeInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly ?Identifier $id;
    public readonly ?Password $passwordOld;
    public readonly ?Password $passwordNew;
    public readonly ?Password $passwordNewRepeat;

    public function __construct(User $userSession, ?string $id, ?string $passwordOld, ?string $passwordNew, ?string $passwordNewRepeat)
    {
        $this->userSession = $userSession;
        $this->id = ValueObjectFactory::createIdentifier($id);
        $this->passwordOld = ValueObjectFactory::createPassword($passwordOld);
        $this->passwordNew = ValueObjectFactory::createPassword($passwordNew);
        $this->passwordNewRepeat = ValueObjectFactory::createPassword($passwordNewRepeat);
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'id' => $this->id,
            'password_old' => $this->passwordOld,
            'password_new' => $this->passwordNew,
            'password_new_repeat' => $this->passwordNewRepeat,
        ]);
    }
}
