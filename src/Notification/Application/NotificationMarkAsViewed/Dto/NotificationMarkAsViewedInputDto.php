<?php

declare(strict_types=1);

namespace Notification\Application\NotificationMarkAsViewed\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class NotificationMarkAsViewedInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $notificationsId;

    /**
     * @param string[]|null $notificationsId
     */
    public function __construct(UserShared $userSession, ?array $notificationsId)
    {
        $this->userSession = $userSession;
        $this->notificationsId = array_map(
            fn (string $notificationId): Identifier => ValueObjectFactory::createIdentifier($notificationId),
            $notificationsId ?? []
        );
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorList = [];
        $errorListNotificationNotEmpty = $validator
            ->setValue($this->notificationsId)
            ->notBlank()
            ->validate();

        if (!empty($errorListNotificationNotEmpty)) {
            $errorList['notifications_empty'] = $errorListNotificationNotEmpty;
        }

        $errorListNotificationsId = $validator->validateValueObjectArray($this->notificationsId);

        if (!empty($errorListNotificationsId)) {
            $errorList['notifications_id'] = $errorListNotificationsId;
        }

        return $errorList;
    }
}
