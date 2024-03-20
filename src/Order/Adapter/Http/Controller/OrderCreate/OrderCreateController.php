<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderCreate;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Order\Adapter\Http\Controller\OrderCreate\Dto\OrderCreateRequestDto;
use Order\Application\OrderCreate\Dto\OrderCreateInputDto;
use Order\Application\OrderCreate\Dto\OrderCreateOutputDto;
use Order\Application\OrderCreate\OrderCreateUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Order')]
#[OA\Post(
    description: 'Creates a order',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id to add the order', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'orders_data', type: 'array', description: 'Order\'s data', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'list_orders_id', type: 'string', description: 'list of orders\' id', example: 'bf1aab1f-8042-41ff-b43d-ada633fb0671'),
                                new OA\Property(property: 'product_id', type: 'string', description: 'Product\' id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                                new OA\Property(property: 'shop_id', type: 'string', description: 'Shop\'s id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                                new OA\Property(property: 'description', type: 'string', description: 'Order\'s description'),
                                new OA\Property(property: 'amount', type: 'float', description: 'Product amount', example: 10.2),
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
            description: 'The order has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Order created'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The order could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|list_orders_id|product_id|orders_empty|list_orders_not_found|product_not_found|shop_not_found|group_error|[], string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class OrderCreateController extends AbstractController
{
    public function __construct(
        private OrderCreateUseCase $OrderCreateUseCase,
        private Security $security
    ) {
    }

    public function __invoke(OrderCreateRequestDto $request): JsonResponse
    {
        $ordersId = $this->OrderCreateUseCase->__invoke(
            $this->createOrderCreateInputDto($request->groupId, $request->ordersData)
        );

        return $this->createResponse($ordersId);
    }

    private function createOrderCreateInputDto(?string $groupId, array $ordersData): OrderCreateInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new OrderCreateInputDto($userAdapter->getUser(), $groupId, $ordersData);
    }

    private function createResponse(OrderCreateOutputDto $OrdersId): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Orders created')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($OrdersId->toArray());

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
