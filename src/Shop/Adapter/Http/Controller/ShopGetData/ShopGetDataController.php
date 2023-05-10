<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopGetData;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
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
            name: 'shop_name_starts_with',
            in: 'query',
            required: false,
            description: 'String for what the shop name starts',
            example: 'Ju',
            schema: new OA\Schema(type: 'string')
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
                                new OA\Property(property: 'id', type: 'string'),
                                new OA\Property(property: 'group_id', type: 'string'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'description', type: 'string'),
                                new OA\Property(property: 'image', type: 'string'),
                                new OA\Property(property: 'created_on', type: 'string'),
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
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions|group_id|shops_id|shops_id|shop_name_starts_with, string|array>')),
                    ]
                )
            )
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
            $this->createShopGetDataInputDto($request->groupId, $request->shopsId, $request->productsId, $request->shopNameStartsWith)
        );

        return $this->createResponse($shops);
    }

    private function createShopGetDataInputDto(string|null $groupId, array|null $shopsId, array|null $productsId, string|null $shopNameStartsWith): ShopGetDataInputDto
    {
        return new ShopGetDataInputDto($groupId, $shopsId, $productsId, $shopNameStartsWith);
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
