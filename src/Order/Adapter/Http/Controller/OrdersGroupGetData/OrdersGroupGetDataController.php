<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrdersGroupGetData;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Order\Adapter\Http\Controller\OrdersGroupGetData\Dto\OrdersGroupGetDataRequestDto;
use Order\Application\OrdersGroupGetData\Dto\OrdersGroupGetDataInputDto;
use Order\Application\OrdersGroupGetData\OrdersGroupGetDataUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Order')]
#[OA\Get(
    description: 'Get a list of orders from a group',
    parameters: [
        new OA\Parameter(
            name: 'group_id',
            in: 'path',
            required: true,
            description: 'Group id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'Number of the page of orders',
            example: 4,
            schema: new OA\Schema(type: 'int')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'Number of items in the page',
            example: 100,
            schema: new OA\Schema(type: 'int')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Orders of the group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Order\'s data'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'string'),
                                new OA\Property(property: 'user_id', type: 'string'),
                                new OA\Property(property: 'group_id', type: 'string'),
                                new OA\Property(property: 'description', type: 'string'),
                                new OA\Property(property: 'amount', type: 'float'),
                                new OA\Property(property: 'created_on', type: 'datetime'),
                                new OA\Property(property: 'product', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string'),
                                        new OA\Property(property: 'image', type: 'string'),
                                        new OA\Property(property: 'created_on', type: 'datetime'),
                                    ]
                                )),
                                new OA\Property(property: 'shop', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string'),
                                        new OA\Property(property: 'image', type: 'string'),
                                        new OA\Property(property: 'created_on', type: 'datetime'),
                                    ]
                                )),
                                new OA\Property(property: 'productShop', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'price', type: 'float'),
                                        new OA\Property(property: 'unit', type: 'string'),
                                    ]
                                )),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Error retrieving orders',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|page|page_items|permissions|list_order_id|orders_not_found, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class OrdersGroupGetDataController extends AbstractController
{
    public function __construct(
        private OrdersGroupGetDataUseCase $OrdersGroupGetDataUseCase,
        private Security $security
    ) {
    }

    public function __invoke(OrdersGroupGetDataRequestDto $request): JsonResponse
    {
        $ordersGroupData = $this->OrdersGroupGetDataUseCase->__invoke(
            $this->createOrdersGroupGetDataInputDto($request->groupId, $request->page, $request->pageItems)
        );

        return $this->createResponse($ordersGroupData);
    }

    private function createOrdersGroupGetDataInputDto(string|null $groupId, int|null $page, int|null $pageItems): OrdersGroupGetDataInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new OrdersGroupGetDataInputDto($userAdapter->getUser(), $groupId, $page, $pageItems);
    }

    private function createResponse(ApplicationOutputInterface $ordersGroupData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Orders of the group data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($ordersGroupData->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
