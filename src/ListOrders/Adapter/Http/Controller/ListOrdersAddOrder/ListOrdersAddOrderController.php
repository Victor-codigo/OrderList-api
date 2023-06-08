<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersAddOrder;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersAddOrder\Dto\ListOrdersAddOrderRequestDto;
use ListOrders\Application\ListOrdersAddOrder\Dto\ListOrdersAddOrderInputDto;
use ListOrders\Application\ListOrdersAddOrder\ListOrdersAddOrderUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Post(
    description: 'Add an order to a list of orders',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'list_orders_id', type: 'string', description: 'list order id, where the orders are added', example: '47bb985d-526c-483f-b167-c425c2725af4'),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'orders', type: 'array', description: 'Orders to add to the list of orders', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'order_id', type: 'string', description: 'Order id', example: '1ab3504f-53fc-4229-85a8-6b4386109fb7'),
                                new OA\Property(property: 'bought', type: 'boolean', description: 'True if order is already bought, false otherwise', example: '1ab3504f-53fc-4229-85a8-6b4386109fb7'),
                            ]
                        )),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The order has been added to the list of orders',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Added order to list of orders'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'list_orders_id', type: 'string', description: 'List of orders id', example: '1ab3504f-53fc-4229-85a8-6b4386109fb7'),
                                new OA\Property(property: 'order_id', type: 'string', description: 'Order id', example: '1ab3504f-53fc-4229-85a8-6b4386109fb7'),
                                new OA\Property(property: 'bought', type: 'string', description: 'True if order is already bought, false otherwise'),
                            ]
                        )),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The order could not be added to the list of orders',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<list_orders_id||list_orders_not_found|group_id|orders_id_empty|orders|orders_already_exists|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersAddOrderController extends AbstractController
{
    public function __construct(
        private ListOrdersAddOrderUseCase $ListOrdersAddOrderUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersAddOrderRequestDto $request): JsonResponse
    {
        $ordersAddedToTheListOrders = $this->ListOrdersAddOrderUseCase->__invoke(
            $this->createListOrdersAddOrderInputDto($request->listOrdersId, $request->groupId, $request->orders)
        );

        return $this->createResponse($ordersAddedToTheListOrders);
    }

    private function createListOrdersAddOrderInputDto(string|null $listOrdersId, string|null $groupId, array|null $orders): ListOrdersAddOrderInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersAddOrderInputDto($userSharedAdapter->getUser(), $listOrdersId, $groupId, $orders);
    }

    private function createResponse(ApplicationOutputInterface $ordersAddedToTheListOrders): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Added order to list of orders')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($ordersAddedToTheListOrders->toArray());

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
