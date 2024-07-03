<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemoveAllUserNotifications\Dto;

use Override;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class NotificationRemoveAllUserNotificationsInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly string $systemKey;

    public function __construct(UserShared $userSession, ?string $systemKey)
    {
        $this->userSession = $userSession;
        $this->systemKey = $systemKey ?? '';
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorListSystemKey = $validator
            ->setValue($this->systemKey)
            ->notBlank()
            ->validate();

        $errorList = [];

        if (!empty($errorListSystemKey)) {
            $errorList['system_key'] = $errorListSystemKey;
        }

        return $errorList;
    }
}
