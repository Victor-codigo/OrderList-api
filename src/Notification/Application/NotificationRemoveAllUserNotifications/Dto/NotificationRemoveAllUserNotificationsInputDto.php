<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemoveAllUserNotifications\Dto;

use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
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

    /**
     * @return array<string, VALIDATION_ERRORS[]>
     */
    #[\Override]
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
