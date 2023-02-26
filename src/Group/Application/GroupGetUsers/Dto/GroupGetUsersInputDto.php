<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers\Dto;

use Common\Domain\Config\AppConfig;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class GroupGetUsersInputDto implements ServiceInputDtoInterface
{
    private const LIMIT_USERS_MAX = AppConfig::ENDPOINT_GROUP_GET_USERS_MAX_USERS;

    public readonly User $userSession;
    public readonly Identifier $groupId;
    public readonly int $limit;
    public readonly int $offset;

    public function __construct(User $userSession, string|null $groupId, int $limit, int $offset)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorListLimit = $validator
            ->setValue($this->limit)
            ->greaterThanOrEqual(1)
            ->lessThanOrEqual(self::LIMIT_USERS_MAX)
            ->validate();

        $errorListOffset = $validator
            ->setValue($this->offset)
            ->greaterThanOrEqual(0)
            ->validate();

        $errorListGroupId = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);

        $errorList = [];
        empty($errorListLimit) ?: $errorList['limit'] = $errorListLimit;
        empty($errorListOffset) ?: $errorList['offset'] = $errorListOffset;
        empty($errorListGroupId) ?: $errorList['group_id'] = $errorListGroupId['group_id'];

        return $errorList;
    }
}
