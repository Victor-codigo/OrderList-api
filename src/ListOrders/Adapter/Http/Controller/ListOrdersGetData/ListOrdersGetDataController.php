<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetData;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersGetData\Dto\ListOrdersGetDataRequestDto;
use ListOrders\Application\ListOrdersGetData\Dto\ListOrdersGetDataInputDto;
use ListOrders\Application\ListOrdersGetData\ListOrdersGetDataUseCase;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Items;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Get(
    description: 'Get list of order\'s data',
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
            name: 'list_orders_id',
            in: 'query',
            required: false,
            description: 'List of orders id separated by comas',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,428e3645-91fb-4239-8b52-b49a056eb2e7',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'order_asc',
            in: 'query',
            required: true,
            description: 'Order of the list of orders',
            example: 'true',
            schema: new OA\Schema(type: 'boolean')
        ),
        new OA\Parameter(
            name: 'filter_value',
            in: 'query',
            required: false,
            description: 'Name of the list order, product, shop',
            example: 'shop name',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'filter_section',
            in: 'query',
            required: false,
            description: 'Section to sort by. (Required if filter_text is set)',
            example: 'listOrders, product or shop',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'filter_text',
            in: 'query',
            required: false,
            description: 'Section to sort by. (Required if filter_section is set)',
            example: 'equals, start_with, end_with, contains',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'Number of page to return',
            example: '1',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'Number of items per page',
            example: '10',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'List of order\'s data',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product\'s data'),
                        new OA\Property(property: 'data', type: 'array', items: new Items(
                            properties: [
                                new OA\Property(property: 'page', type: 'integer'),
                                new OA\Property(property: 'pages_total', type: 'integer'),
                                new OA\Property(property: 'list_orders', type: 'array', items: new Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string'),
                                        new OA\Property(property: 'user_id', type: 'string'),
                                        new OA\Property(property: 'group_id', type: 'string'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string'),
                                        new OA\Property(property: 'date_to_buy', type: 'string'),
                                        new OA\Property(property: 'created_on', type: 'string'),
                                    ]
                                )),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The list of orders could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new Items(default: '<group_id|list_orders_id|list_orders_not_found|section_filter_type|text_filter_type|text_filter_value|filter_section_and_text_not_empty|page|page_items|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersGetDataController extends AbstractController
{
    public function __construct(
        private ListOrdersGetDataUseCase $ListOrdersGetDataUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersGetDataRequestDto $request): JsonResponse
    {
        $listOrderData = $this->ListOrdersGetDataUseCase->__invoke(
            $this->createListOrdersGetDataInputDto(
                $request->groupId,
                $request->listOrdersIds,
                $request->filterValue,
                $request->orderAsc,
                $request->filterSection,
                $request->filterText,
                $request->page,
                $request->pageItems
            )
        );

        return $this->createResponse($listOrderData);
    }

    private function createListOrdersGetDataInputDto(?string $groupId, ?array $listOrdersIds, ?string $filterValue, bool $orderAsc, ?string $filterSection, ?string $filterText, ?int $page, ?int $pageItems): ListOrdersGetDataInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersGetDataInputDto(
            $userSharedAdapter->getUser(),
            $groupId,
            $listOrdersIds,
            $filterValue,
            $orderAsc,
            $filterSection,
            $filterText,
            $page,
            $pageItems
        );
    }

    private function createResponse(ApplicationOutputInterface $listOrderData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrderData->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
