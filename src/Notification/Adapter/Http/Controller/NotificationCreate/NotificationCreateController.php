<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationCreate;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Notification\Adapter\Http\Controller\NotificationCreate\Dto\NotificationCreateRequestDto;
use Notification\Application\NotificationCreate\Dto\NotificationCreateInputDto;
use Notification\Application\NotificationCreate\Dto\NotificationCreateOutputDto;
use Notification\Application\NotificationCreate\NotificationCreateUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

#[OA\Tag('Notification')]
#[OA\Post(
    description: 'Creates a notification or notifications',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'users_id', type: 'array', items: new OA\Items('string'), description: 'User\'s id to send notification', example: '[22fd9f1f-ff4c-4f4a-abca-b0be7f965048]'),
                        new OA\Property(property: 'type', type: 'string', description: 'Notification type', example: 'NOTIFICATION_TYPE::*'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The group has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Group created'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The group could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<users_id|users_wrong|type, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class NotificationCreateController extends AbstractController
{
    public function __construct(
        private NotificationCreateUseCase $NotificationCreateUseCase,
        private Security $security
    ) {
    }

    public function __invoke(NotificationCreateRequestDto $request): JsonResponse
    {
        $notification = $this->NotificationCreateUseCase->__invoke(
            $this->createNotificationCreateInputDto($request->userId, $request->notificationType)
        );

        return $this->createResponse($notification);
    }

    /**
     * @param string[]|null $userId
     */
    private function createNotificationCreateInputDto(array|null $userId, string|null $notificationType): NotificationCreateInputDto
    {
        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new NotificationCreateInputDto($userAdapter->getUser(), $userId, $notificationType);
    }

    private function createResponse(NotificationCreateOutputDto $notificationOutput): JsonResponse
    {
        $notificationsIds = array_map(
            fn (Identifier $notificationId) => $notificationId->getValue(),
            $notificationOutput->notificationIds
        );

        $responseDto = (new ResponseDto())
            ->setMessage('Notification created')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData(['id' => $notificationsIds]);

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
