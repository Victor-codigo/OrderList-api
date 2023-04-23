<?php

declare(strict_types=1);

namespace Notification\Application\NotificationCreate\Dto;

use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class NotificationCreateInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $usersId;
    public readonly NotificationType $notificationType;
    public readonly NotificationData $notificationData;
    public readonly string $systemKey;

    /**
     * @param string[]|null $userId
     */
    public function __construct(User $userSession, array|null $usersId, string|null $notificationType, array|null $notificationData, string|null $systemKey)
    {
        $this->systemKey = null === $systemKey ? '' : $systemKey;
        $this->notificationData = ValueObjectFactory::createNotificationData($notificationData);
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
            'notification_data' => $this->notificationData,
        ]);

        $errorListUsersId = $this->validateUsersId($validator);
        $errorListSystemKey = $this->validateSystemKey($validator);

        if (!empty($errorListUsersId)) {
            $errorList['users_id'] = $errorListUsersId;
        }

        if (!empty($errorListSystemKey)) {
            $errorList['system_key'] = $errorListSystemKey;
        }

        return $errorList;
    }

    private function validateUsersId(ValidationInterface $validator): array
    {
        $errorList = $validator
            ->setValue($this->usersId)
            ->notNull()
            ->notBlank()
            ->validate();

        if (!empty($errorList)) {
            return $errorList;
        }

        $errorList = $validator->validateValueObjectArray($this->usersId);
        $errorList = array_reduce(
            $errorList,
            fn (array $carry, array $errorLIstUserId) => array_merge($carry, $errorLIstUserId),
            []
        );

        return array_unique($errorList, SORT_REGULAR);
    }

    private function validateSystemKey(ValidationInterface $validator): array
    {
        $errorListSystemKey = $validator
            ->setValue($this->systemKey)
            ->notNull()
            ->notBlank()
            ->validate();

        return $errorListSystemKey;
    }
}
