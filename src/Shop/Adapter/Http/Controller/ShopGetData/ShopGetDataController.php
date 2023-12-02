<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopGetData;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use OpenApi\Attributes as OA;
use Shop\Adapter\Http\Controller\ShopGetData\Dto\ShopGetDataRequestDto;
use Shop\Application\ShopGetData\Dto\ShopGetDataInputDto;
use Shop\Application\ShopGetData\ShopGetDataUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Shop')]
#[OA\Get(
    description: 'Get shop\'s data',
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
            name: 'page',
            in: 'query',
            required: true,
            description: 'Page number',
            example: 1,
            schema: new OA\Schema(type: 'int')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'Number of items per page',
            example: 100,
            schema: new OA\Schema(type: 'int')
        ),
        new OA\Parameter(
            name: 'shops_id',
            in: 'query',
            required: false,
            description: 'Shops id separated by comas',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,428e3645-91fb-4239-8b52-b49a056eb2e7',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'products_id',
            in: 'query',
            required: false,
            description: 'Products id separated by comas',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,428e3645-91fb-4239-8b52-b49a056eb2e7',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'shop_name',
            in: 'query',
            required: false,
            description: 'Name of the shop',
            example: 'shop name',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'order_asc',
            in: 'query',
            required: false,
            description: 'TRUE if you want to order by asc, otherwise FALSE',
            example: 'true',
            schema: new OA\Schema(type: 'boolean')
        ),
        new OA\Parameter(
            name: 'shop_name_filter_type',
            in: 'query',
            required: false,
            description: 'Type of the filter to apply. It is mandatory to pass two shop_name_filter parameters, to apply filter',
            example: FILTER_STRING_COMPARISON::STARTS_WITH,
            schema: new OA\Schema(
                type: 'string',
                enum: [
                    FILTER_STRING_COMPARISON::STARTS_WITH,
                    FILTER_STRING_COMPARISON::ENDS_WITH,
                    FILTER_STRING_COMPARISON::CONTAINS,
                    FILTER_STRING_COMPARISON::EQUALS,
                ]
            ),
        ),
        new OA\Parameter(
            name: 'shop_name_filter_value',
            in: 'query',
            required: false,
            description: 'Value of the filter to apply. It is mandatory to pass two shop_name_filter parameters, to apply filter',
            example: 'Shop',
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Shop\'s data',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Shop\'s data'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'page', type: 'int'),
                                new OA\Property(property: 'pages_total', type: 'int'),
                                new OA\Property(property: 'shops', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string'),
                                        new OA\Property(property: 'group_id', type: 'string'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string'),
                                        new OA\Property(property: 'image', type: 'string'),
                                        new OA\Property(property: 'created_on', type: 'string'),
                                    ])),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Shops could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<not_enough_parameters|permissions|group_id|shops_id|products_id|shop_name|shop_filter_value|shop_filter_type|page|page_items, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'Shops not be found',
        ),
    ]
)]
class ShopGetDataController extends AbstractController
{
    public function __construct(
        private ShopGetDataUseCase $shopGetDataUseCase
    ) {
    }

    public function __invoke(ShopGetDataRequestDto $request): JsonResponse
    {
        $shops = $this->shopGetDataUseCase->__invoke(
            $this->createShopGetDataInputDto(
                $request->groupId,
                $request->shopsId,
                $request->productsId,
                $request->shopNameFilterType,
                $request->shopNameFilterValue,
                $request->shopName,
                $request->orderArc,
                $request->page,
                $request->pageItems
            )
        );

        return $this->createResponse($shops);
    }

    private function createShopGetDataInputDto(
        string|null $groupId,
        array|null $shopsId,
        array|null $productsId,
        string|null $shopNameFilterType,
        string|int|float|null $shopNameFilterValue,
        string|null $shopName,
        bool|null $orderAsc,
        int|null $page,
        int|null $pageItems,
    ): ShopGetDataInputDto {
        return new ShopGetDataInputDto(
            $groupId,
            $shopsId,
            $productsId,
            $shopNameFilterType,
            $shopNameFilterValue,
            $shopName,
            $orderAsc,
            $page,
            $pageItems
        );
    }

    private function createResponse(ApplicationOutputInterface $shops): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Shops data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($shops->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
