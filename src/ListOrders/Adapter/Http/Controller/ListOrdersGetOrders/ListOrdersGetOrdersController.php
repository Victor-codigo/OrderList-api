<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetOrders;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersGetOrders\Dto\ListOrdersGetOrdersRequestDto;
use ListOrders\Application\ListOrdersGetOrders\Dto\ListOrdersGetOrdersInputDto;
use ListOrders\Application\ListOrdersGetOrders\ListOrdersGetOrdersUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use openApi\Attributes as OA;

#[OA\Tag('ListOrders')]
#[OA\Get(
    description: 'Get a list of orders from a list of orders',
    parameters: [
        new OA\Parameter(
            name: 'group_id',
            in: 'query',
            required: true,
            description: 'Group id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'list_order_id',
            in: 'query',
            required: true,
            description: 'List of orders id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,428e3645-91fb-4239-8b52-b49a056eb2e7',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'number of the page of orders',
            example: 4,
            schema: new OA\Schema(type: 'int')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'number of items in the page',
            example: 100,
            schema: new OA\Schema(type: 'int')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Orders of the list of orders',
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
                                new OA\Property(property: 'unit', type: 'string'),
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
class ListOrdersGetOrdersController extends AbstractController
{
    public function __construct(
        private ListOrdersGetOrdersUseCase $ListOrdersGetOrdersUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersGetOrdersRequestDto $request): JsonResponse
    {
        $orderListOrdersData = $this->ListOrdersGetOrdersUseCase->__invoke(
            $this->createListOrdersGetOrdersInputDto($request->groupId, $request->listOrderId, $request->page, $request->page_items)
        );

        return $this->createResponse($orderListOrdersData);
    }

    private function createListOrdersGetOrdersInputDto(string|null $groupId, string|null $listOrderId, int|null $page, int|null $pageItems): ListOrdersGetOrdersInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersGetOrdersInputDto($userSharedAdapter->getUser(), $groupId, $listOrderId, $page, $pageItems);
    }

    private function createResponse(ApplicationOutputInterface $orderListOrdersData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Orders list orders data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($orderListOrdersData->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
