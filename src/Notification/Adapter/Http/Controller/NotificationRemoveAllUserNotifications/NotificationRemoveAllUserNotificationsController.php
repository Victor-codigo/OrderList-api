<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationRemoveAllUserNotifications;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Notification\Adapter\Http\Controller\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsRequestDto;
use Notification\Application\NotificationRemoveAllUserNotifications\Dto\NotificationRemoveAllUserNotificationsInputDto;
use Notification\Application\NotificationRemoveAllUserNotifications\NotificationRemoveAllUserNotificationsUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Notification')]
#[OA\Delete(
    description: 'Removes all user session notifications',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'system_key', type: 'string', description: 'System ke', example: 'asgasrhaetjr'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The notifications has been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'User notifications removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'There is no notifications',
            content: new OA\MediaType(
                mediaType: 'application/json'
            )
        ),
    ]
)]
class NotificationRemoveAllUserNotificationsController extends AbstractController
{
    public function __construct(
        private NotificationRemoveAllUserNotificationsUseCase $notificationRemoveAllUserNotificationsUseCase,
        private Security $security
    ) {
    }

    public function __invoke(NotificationRemoveAllUserNotificationsRequestDto $request): JsonResponse
    {
        $notifications = $this->notificationRemoveAllUserNotificationsUseCase->__invoke(
            $this->createNotificationRemoveAllUserNotificationsInputDto($request->systemKey)
        );

        return $this->createResponse($notifications);
    }

    private function createNotificationRemoveAllUserNotificationsInputDto(?string $systemKey): NotificationRemoveAllUserNotificationsInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new NotificationRemoveAllUserNotificationsInputDto($userSharedAdapter->getUser(), $systemKey);
    }

    private function createResponse(ApplicationOutputInterface $notifications): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('User notifications removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData(['id' => $notifications->toArray()]);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
