<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class NotificationRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $notificationIds;

    public function __construct(User $userSession, array|null $notificationsId)
    {
        $this->userSession = $userSession;
        $this->notificationIds = null === $notificationsId
            ? []
            : array_map(
                fn (string $notificationId) => ValueObjectFactory::createIdentifier($notificationId),
                $notificationsId
            );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorsList = $validator
            ->setValue($this->notificationIds)
            ->notBlank()
            ->validate();

        if (!empty($errorsList)) {
            return ['notifications_id' => $errorsList];
        }

        $errorsList = $validator->validateValueObjectArray($this->notificationIds);

        return empty($errorsList) ? [] : ['notifications_id' => $errorsList[0]];
    }
}
