<?php

declare(strict_types=1);

namespace Share\Adapter\Http\Controller\ShareListOrdersGetData;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Share\Adapter\Http\Controller\ShareListOrdersGetData\Dto\ShareListOrdersGetDataRequestDto;
use Share\Application\ShareListOrdersGetData\Dto\ShareListOrdersGetDataInputDto;
use Share\Application\ShareListOrdersGetData\ShareListOrdersGetDataUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Share')]
#[OA\Get(
    description: 'Get a shared list of orders data',
    parameters: [
        new OA\Parameter(
            name: 'shared_list_orders_id',
            in: 'query',
            required: true,
            description: 'Shared list orders id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'Page number of the list of orders',
            example: 1,
            schema: new OA\Schema(type: 'int')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'Number or items per page of the list of orders',
            example: 1,
            schema: new OA\Schema(type: 'int')
        ),
        new OA\Parameter(
            name: 'filter_text',
            in: 'query',
            required: false,
            description: 'Test filter to use: start_with, end_with, contains, equals',
            example: 'start_with',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'filter_value',
            in: 'query',
            required: false,
            description: 'Word to filter by',
            example: 'potato',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'Shared list of orders data',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'List orders shared data'),
                        new OA\Property(property: 'data', type: 'object',
                            properties: [
                                new OA\Property(property: 'page', type: 'int', description: 'Page number', example: 1),
                                new OA\Property(property: 'pages_total', type: 'int', description: 'Number of pages', example: 10),
                                new OA\Property(property: 'list_orders', type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string'),
                                        new OA\Property(property: 'user_id', type: 'string'),
                                        new OA\Property(property: 'group_id', type: 'string'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string'),
                                        new OA\Property(property: 'date_to_buy', type: 'string'),
                                        new OA\Property(property: 'created_on', type: 'string'),
                                    ]
                                ),
                                new OA\Property(property: 'orders', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string'),
                                        new OA\Property(property: 'group_id', type: 'string'),
                                        new OA\Property(property: 'list_orders_id', type: 'string'),
                                        new OA\Property(property: 'user_id', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string'),
                                        new OA\Property(property: 'amount', type: 'float'),
                                        new OA\Property(property: 'bought', type: 'bool'),
                                        new OA\Property(property: 'created_on', type: 'float'),
                                        new OA\Property(property: 'product', type: 'object',
                                            properties: [
                                                new OA\Property(property: 'id', type: 'string'),
                                                new OA\Property(property: 'name', type: 'string'),
                                                new OA\Property(property: 'description', type: 'string'),
                                                new OA\Property(property: 'image', type: 'string'),
                                                new OA\Property(property: 'created_on', type: 'string'),
                                            ]
                                        ),
                                        new OA\Property(property: 'shop', type: 'object',
                                            properties: [
                                                new OA\Property(property: 'id', type: 'string'),
                                                new OA\Property(property: 'name', type: 'string'),
                                                new OA\Property(property: 'address', type: 'string'),
                                                new OA\Property(property: 'description', type: 'string'),
                                                new OA\Property(property: 'image', type: 'string'),
                                                new OA\Property(property: 'created_on', type: 'string'),
                                            ]
                                        ),
                                        new OA\Property(property: 'product_shop', type: 'object',
                                            properties: [
                                                new OA\Property(property: 'price', type: 'float'),
                                                new OA\Property(property: 'unit', type: 'string'),
                                            ]
                                        ),
                                    ]
                                )),
                            ]),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The shared list of orders could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<shared_list_orders_id|list_orders_not_found|page|page_items|text_filter_type|text_filter_value, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ShareListOrdersGetDataController extends AbstractController
{
    public function __construct(
        private ShareListOrdersGetDataUseCase $sharedListOrdersGetDataUseCase,
    ) {
    }

    public function __invoke(ShareListOrdersGetDataRequestDto $request): JsonResponse
    {
        $sharedListOrdersGetData = $this->sharedListOrdersGetDataUseCase->__invoke(
            $this->createListOrdersSharedGetDataInputDto($request->sharedListOrdersId, $request->page, $request->pageItems, $request->filterText, $request->filterValue)
        );

        return $this->createResponse($sharedListOrdersGetData);
    }

    private function createListOrdersSharedGetDataInputDto(?string $sharedListOrdersId, ?int $page, ?int $pageItems, ?string $filterText, ?string $filterValue): ShareListOrdersGetDataInputDto
    {
        return new ShareListOrdersGetDataInputDto(
            $sharedListOrdersId,
            $page,
            $pageItems,
            $filterText,
            $filterValue
        );
    }

    private function createResponse(ApplicationOutputInterface $sharedList): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders shared data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($sharedList->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
