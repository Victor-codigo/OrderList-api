<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderRemove;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Order\Adapter\Http\Controller\OrderRemove\Dto\OrderRemoveRequestDto;
use Order\Application\OrderRemove\Dto\OrderRemoveInputDto;
use Order\Application\OrderRemove\OrderRemoveUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Order')]
#[OA\Delete(
    description: 'Removes some orders',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id of the order', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'orders_id', type: 'array', description: 'Order\'s id', items: new OA\Items(type: 'string')),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The orders has been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Order removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The order could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|orders_empty|orders_id|permissions|orders_not_found, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class OrderRemoveController extends AbstractController
{
    public function __construct(
        private OrderRemoveUseCase $OrderRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(OrderRemoveRequestDto $request): JsonResponse
    {
        $ordersRemovedId = $this->OrderRemoveUseCase->__invoke(
            $this->createOrderRemoveInputDto($request->ordersId, $request->groupId)
        );

        return $this->createResponse($ordersRemovedId);
    }

    private function createOrderRemoveInputDto(?array $ordersId, ?string $groupId): OrderRemoveInputDto
    {
        /** @var UserSharedSymfonyAdapter $userShared */
        $userShared = $this->security->getUser();

        return new OrderRemoveInputDto($userShared->getUser(), $ordersId, $groupId);
    }

    private function createResponse(ApplicationOutputInterface $ordersRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Orders removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($ordersRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
