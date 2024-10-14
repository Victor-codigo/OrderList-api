<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class NotificationRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $notificationIds;

    /**
     * @param string[]|null $notificationsId
     */
    public function __construct(UserShared $userSession, ?array $notificationsId)
    {
        $this->userSession = $userSession;
        $this->notificationIds = null === $notificationsId
            ? []
            : array_map(
                fn (string $notificationId): Identifier => ValueObjectFactory::createIdentifier($notificationId),
                $notificationsId
            );
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
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
