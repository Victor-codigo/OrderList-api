<?php

declare(strict_types=1);

namespace Notification\Application\NotificationCreate\Dto;

use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use User\Domain\Model\User;

class NotificationCreateInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $usersId;
    public readonly NotificationType $notificationType;
    public readonly string $systemKey;

    /**
     * @param string[]|null $userId
     */
    public function __construct(User $userSession, array|null $usersId, string|null $notificationType, string|null $systemKey)
    {
        $this->systemKey = null === $systemKey ? '' : $systemKey;
        $this->userSession = $userSession;
        $this->usersId = null === $usersId
            ? []
            : array_map(
                fn (string $userId) => ValueObjectFactory::createIdentifier($userId),
                $usersId
            );
        $this->notificationType = ValueObjectFactory::createNotificationType(
            null === $notificationType ? null : NOTIFICATION_TYPE::tryFrom($notificationType)
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'type' => $this->notificationType,
        ]);

        $errorListUsersIdArray = $validator
            ->setValue($this->usersId)
            ->notNull()
            ->notBlank()
            ->validate();

        if (!empty($errorListUsersIdArray)) {
            $errorList['users_id'] = $errorListUsersIdArray;
        }

        $errorListUsersId = $validator->validateValueObjectArray($this->usersId);

        if (!empty($errorListUsersId)) {
            $errorList['users_id'] = $errorListUsersId[0];
        }

        $errorListSystemKey = $validator
            ->setValue($this->systemKey)
            ->notNull()
            ->notBlank()
            ->validate();

        if (!empty($errorListSystemKey)) {
            $errorList['system_key'] = $errorListSystemKey;
        }

        return $errorList;
    }
}
