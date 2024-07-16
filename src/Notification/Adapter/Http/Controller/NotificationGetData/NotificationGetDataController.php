<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationGetData;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Notification\Adapter\Http\Controller\NotificationGetData\Dto\NotificationGetDataRequestDto;
use Notification\Application\NotificationGetData\Dto\NotificationGetDataInputDto;
use Notification\Application\NotificationGetData\NotificationGetDataUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Notification')]
#[OA\Get(
    description: 'Get notifications information',
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'Number of the notification page',
            example: '1',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'Number of notifications per page',
            example: '1',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'lang',
            in: 'query',
            required: true,
            description: 'Language of the notifications',
            example: 'en|es',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Notifications found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Notifications data'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'string', example: '1fcab788-0def-4e56-b441-935361678da9'),
                                new OA\Property(property: 'user_id', type: 'string', example: '2606508b-4516-45d6-93a6-c7cb416b7f3f'),
                                new OA\Property(property: 'message', type: 'string', example: 'This is an example of notification'),
                                new OA\Property(property: 'viewed', type: 'bool'),
                                new OA\Property(property: 'created_on', type: 'string', example: '2023-2-14 14:05:10'),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'Notifications not found'
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Notifications request error',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<page|page_items|lang, string>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You are not logged in',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema()
            )
        ),
    ]
)]
class NotificationGetDataController extends AbstractController
{
    public function __construct(
        private NotificationGetDataUseCase $NotificationGetDataUseCase,
        private Security $security
    ) {
    }

    public function __invoke(NotificationGetDataRequestDto $request): JsonResponse
    {
        $notifications = $this->NotificationGetDataUseCase->__invoke(
            $this->createNotificationGetDataInputDto($request->page, $request->pageItems, $request->lang)
        );

        return $this->createResponse($notifications->notificationsData);
    }

    private function createNotificationGetDataInputDto(?int $page, ?int $pageItems, ?string $lang): NotificationGetDataInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new NotificationGetDataInputDto($userSharedAdapter->getUser(), $page, $pageItems, $lang);
    }

    private function createResponse(array $notificationsData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Notifications data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($notificationsData);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
