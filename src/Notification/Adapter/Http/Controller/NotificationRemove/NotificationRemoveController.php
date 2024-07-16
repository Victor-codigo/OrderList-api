<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationRemove;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Notification\Adapter\Http\Controller\NotificationRemove\Dto\NotificationRemoveRequestDto;
use Notification\Application\NotificationRemove\Dto\NotificationRemoveInputDto;
use Notification\Application\NotificationRemove\Dto\NotificationRemoveOutputDto;
use Notification\Application\NotificationRemove\NotificationRemoveUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Notification')]
#[OA\Delete(
    description: 'Removes notifications',
    parameters: [
        new OA\Parameter(
            name: 'notifications_id',
            in: 'path',
            required: true,
            description: 'a list of notifications id separated by a coma',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,1fcab788-0def-4e56-b441-935361678da9',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The notifications has been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Notifications removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The notification could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<notifications_id, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'The notification could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema()
            )
        ),
    ]
)]
class NotificationRemoveController extends AbstractController
{
    public function __construct(
        private NotificationRemoveUseCase $NotificationRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(NotificationRemoveRequestDto $request): JsonResponse
    {
        $notifications = $this->NotificationRemoveUseCase->__invoke(
            $this->createNotificationRemoveInputDto($request->notificationsId)
        );

        return $this->createResponse($notifications);
    }

    /**
     * @param string[]|null $notificationsId
     */
    private function createNotificationRemoveInputDto(?array $notificationsId): NotificationRemoveInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new NotificationRemoveInputDto($userSharedAdapter->getUser(), $notificationsId);
    }

    private function createResponse(NotificationRemoveOutputDto $notifications): JsonResponse
    {
        $notificationsId = array_map(
            fn (Identifier $notificationId): ?string => $notificationId->getValue(),
            $notifications->notificationsId
        );

        $responseDto = (new ResponseDto())
            ->setMessage('Notifications removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData(['id' => $notificationsId]);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
