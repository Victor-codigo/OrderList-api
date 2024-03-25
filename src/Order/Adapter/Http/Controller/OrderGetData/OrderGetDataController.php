<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderGetData;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Order\Adapter\Http\Controller\OrderGetData\Dto\OrderGetDataRequestDto;
use Order\Application\OrderGetData\Dto\OrderGetDataInputDto;
use Order\Application\OrderGetData\OrderGetDataUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Order')]
#[OA\Get(
    description: 'Get order\'s data',
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
            name: 'orders_id',
            in: 'query',
            required: false,
            description: 'Orders id separated by comas',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,428e3645-91fb-4239-8b52-b49a056eb2e7',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'list_orders_id',
            in: 'query',
            required: false,
            description: 'List orders id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'Page number',
            example: 1,
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'Number of items per page',
            example: 100,
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'order_asc',
            in: 'query',
            required: true,
            description: 'Order ascendent or descendent',
            example: true,
            schema: new OA\Schema(type: 'boolean')
        ),
        new OA\Parameter(
            name: 'filter_section',
            in: 'query',
            required: false,
            description: 'Section filter',
            example: 'orders',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'filter_text',
            in: 'query',
            required: false,
            description: 'Name filter',
            example: 'equals',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'filter_value',
            in: 'query',
            required: false,
            description: 'Value of the filter',
            example: 'some name',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Order\'s data',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product\'s data'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'page', type: 'integer'),
                                new OA\Property(property: 'pages_total', type: 'integer'),
                                new OA\Property(property: 'orders', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string'),
                                        new OA\Property(property: 'group_id', type: 'string'),
                                        new OA\Property(property: 'list_orders_id', type: 'string'),
                                        new OA\Property(property: 'user_id', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string'),
                                        new OA\Property(property: 'amount', type: 'float'),
                                        new OA\Property(property: 'bought', type: 'boolean'),
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
            description: 'The product could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|list_orders_id|orders_id|page|page_items|section_filter_type|filter_section_and_text_not_empty|text_filter_type|order_not_found|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class OrderGetDataController extends AbstractController
{
    public function __construct(
        private OrderGetDataUseCase $orderGetDataUseCase,
        private Security $security
    ) {
    }

    public function __invoke(OrderGetDataRequestDto $request): JsonResponse
    {
        $ordersData = $this->orderGetDataUseCase->__invoke(
            $this->createOrderGetDataInputDto(
                $request->groupId,
                $request->listOrdersId,
                $request->ordersId,
                $request->page,
                $request->pageItems,
                $request->orderAsc,
                $request->filterSection,
                $request->filterText,
                $request->filterValue
            )
        );

        return $this->createResponse($ordersData);
    }

    private function createOrderGetDataInputDto(?string $groupId, ?string $listOrdersId, ?array $ordersId, ?int $page, ?int $pageItems, bool $orderAsc, ?string $filterSection, ?string $filterText, ?string $filterValue): OrderGetDataInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new OrderGetDataInputDto(
            $userSharedAdapter->getUser(),
            $groupId,
            $listOrdersId,
            $ordersId,
            $page,
            $pageItems,
            $orderAsc,
            $filterSection,
            $filterText,
            $filterValue
        );
    }

    private function createResponse(ApplicationOutputInterface $ordersData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Orders\' data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($ordersData->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
