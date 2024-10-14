<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationGetData;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Translator\TranslatorInterface;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Notification\Domain\Model\Notification;
use Notification\Domain\Ports\Notification\NotificationRepositoryInterface;
use Notification\Domain\Service\NotificationGetData\Dto\NotificationGetDataDto;

class NotificationGetDataService
{
    private const string TRANSLATION_DOMAIN = 'Notifications';

    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<int, array{
     *  id: string|null,
     *  user_id: string|null,
     *  message: string|null,
     *  viewed: bool,
     *  created_on: string,
     * }>
     *
     * @throws DBNotFoundException
     * @throws LogicException
     */
    public function __invoke(NotificationGetDataDto $input): array
    {
        $notifications = $this->notificationRepository->getNotificationByUserIdOrFail($input->userId);
        $notifications->setPagination($input->page->getValue(), $input->pageItems->getValue());

        return $this->getNotificationsData($notifications, $input->lang);
    }

    /**
     * @param PaginatorInterface<int, Notification> $notifications
     *
     * @return array<int, array{
     *  id: string|null,
     *  user_id: string|null,
     *  message: string|null,
     *  viewed: bool,
     *  created_on: string,
     * }>
     */
    private function getNotificationsData(PaginatorInterface $notifications, Language $lang): array
    {
        $notificationsData = [];
        /** @var Notification $notification */
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
    private function translateNotification(Language $lang, NotificationType $notificationType, NotificationData $notificationData): string
    {
        $translateKey = match ($notificationType->getValue()) {
            NOTIFICATION_TYPE::GROUP_CREATED => 'notification.group.created',
            NOTIFICATION_TYPE::GROUP_REMOVED => 'notification.group.removed',
            NOTIFICATION_TYPE::GROUP_USER_ADDED => 'notification.group.user_added',
            NOTIFICATION_TYPE::GROUP_USER_REMOVED => 'notification.group.user_removed',
            NOTIFICATION_TYPE::GROUP_USER_SET_AS_ADMIN => 'notification.group.set_as_admin',

            NOTIFICATION_TYPE::USER_EMAIL_CHANGED => 'notification.user.email_changed',
            NOTIFICATION_TYPE::USER_PASSWORD_CHANGED => 'notification.user.password_changed',
            NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER => 'notification.user.password_remembered',
            NOTIFICATION_TYPE::USER_REGISTERED => 'notification.user.registered',
            default => throw LogicException::fromMessage('Notification type not found'),
        };

        return $this->translator->translate(
            $translateKey,
            $notificationData->getValue(),
            self::TRANSLATION_DOMAIN,
            $lang->getValue()
        );
    }
}
