<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class GroupUserRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $usersId;

    public function __construct(User $userSession, string|null $groupId, array|null $usersId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->usersId = null === $usersId ? [] : array_map(
            fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
            $usersId
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
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
