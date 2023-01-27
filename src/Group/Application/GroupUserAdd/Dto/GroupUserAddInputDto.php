<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Dto;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use Group\Domain\Model\GROUP_ROLES;
use User\Domain\Model\User;

class GroupUserAddInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $usersId;
    public readonly Rol $rol;

    /**
     * @param Identifier[] $users
     */
    public function __construct(User $userSession, string|null $groupId, array|null $usersId, bool|null $admin)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->usersId = null === $usersId ? [] : array_map(
            fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
            $usersId
        );
        $this->rol = ValueObjectFactory::createRol($admin ? GROUP_ROLES::ADMIN : GROUP_ROLES::USER);
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'admin' => $this->rol,
        ]);

        $usersIdErrorListNotBlank = $validator
            ->setValue($this->usersId)
            ->notBlank()
            ->validate();

        $usersIdErrorListValueObject = $validator->validateValueObjectArray($this->usersId);

        // flat user errors
        $usersIdErrorList = [];
        array_walk_recursive($usersIdErrorListValueObject, function ($error) use (&$usersIdErrorList) { $usersIdErrorList[] = $error; });
        $usersIdErrorList = array_merge($usersIdErrorListNotBlank, $usersIdErrorList);
        $usersIdErrorList = array_unique($usersIdErrorList, SORT_REGULAR);

        if (!empty($usersIdErrorList)) {
            $errorList['users'] = $usersIdErrorList;
        }

        return $errorList;
    }
}
