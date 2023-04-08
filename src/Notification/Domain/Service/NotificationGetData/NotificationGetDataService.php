<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationGetData;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Translator\TranslatorInterface;
use Notification\Domain\Model\NOTIFICATION_TYPE;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationGetData\Dto\NotificationGetDataDto;

class NotificationGetDataService
{
    private const TRANSLATION_DOMAIN = 'Notifications';

    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws LogicException
     */
    public function __invoke(NotificationGetDataDto $input): array
    {
        $notifications = $this->notificationRepository->getNotificationByUserIdOrFail($input->userId);
        $notifications->setPagination($input->page->getValue(), $input->pageItems->getValue());

        return $this->getNotificationsData($notifications, $input->lang);
    }

    private function getNotificationsData(PaginatorInterface $notifications, Language $lang): array
    {
        $notificationsData = [];
        foreach ($notifications as $notification) {
            $notificationsData[] = [
                'id' => $notification->getId()->getValue(),
                'user_id' => $notification->getUserId()->getValue(),
                'message' => $this->translateNotification($lang, $notification->getType(), $notification->getData()),
                'viewed' => $notification->getViewed(),
                'created_on' => $notification->getCreatedOn()->format('Y-m-d H:i:s'),
            ];
        }

        return $notificationsData;
    }

    /**
     * @throws LogicException
     */
    private function translateNotification(language $lang, NotificationType $notificationType, NotificationData $notificationData): string
    {
        return match ($notificationType->getValue()) {
            NOTIFICATION_TYPE::GROUP_USER_ADDED => $this->translator->translate(
                'notification.group_user_added',
                $notificationData->getValue(),
                self::TRANSLATION_DOMAIN,
                $lang->getValue()
            ),
            NOTIFICATION_TYPE::USER_REGISTERED => $this->translator->translate(
                'notification.user_registered',
                $notificationData->getValue(),
                self::TRANSLATION_DOMAIN,
                $lang->getValue()
            ),
            default => throw LogicException::fromMessage('Notification type not found')
        };
    }
}
