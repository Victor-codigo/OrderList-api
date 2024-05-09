<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationMarkAsViewed;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Notification\Adapter\Http\Controller\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedRequestDto;
use Notification\Application\NotificationMarkAsViewed\Dto\NotificationMarkAsViewedInputDto;
use Notification\Application\NotificationMarkAsViewed\NotificationMarkAsViewedUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Notification')]
#[OA\Patch(
    description: 'Mark a notification as viewed',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'notifications_id', type: 'array', items: new OA\Items('string'), description: 'Notifications id', example: '[22fd9f1f-ff4c-4f4a-abca-b0be7f965048]'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'Notifications marked as viewed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Notifications marked as viewed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Notifications cannot be marked as viewed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<notifications_empty|notifications_id|notifications_not_found, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class NotificationMarkAsViewedController extends AbstractController
{
    public function __construct(
        private NotificationMarkAsViewedUseCase $notificationMarkAsViewedUseCase,
        private Security $security
    ) {
    }

    public function __invoke(NotificationMarkAsViewedRequestDto $request): JsonResponse
    {
        $notification = $this->notificationMarkAsViewedUseCase->__invoke(
            $this->createNotificationMarkAsViewedInputDto($request->notificationsId)
        );

        return $this->createResponse($notification);
    }

    private function createNotificationMarkAsViewedInputDto(?array $notificationsId): NotificationMarkAsViewedInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new NotificationMarkAsViewedInputDto($userSharedAdapter->getUser(), $notificationsId);
    }

    private function createResponse(ApplicationOutputInterface $notificationOutput): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Notifications marked as viewed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData(['id' => $notificationOutput->toArray()]);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
