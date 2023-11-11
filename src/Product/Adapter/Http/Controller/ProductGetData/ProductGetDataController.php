<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductGetData;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Product\Adapter\Http\Controller\ProductGetData\Dto\ProductGetDataRequestDto;
use Product\Application\ProductGetData\Dto\ProductGetDataInputDto;
use Product\Application\ProductGetData\ProductGetDataUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Get(
    description: 'Get product\'s data',
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
            name: 'products_id',
            in: 'query',
            required: false,
            description: 'Products id separated by comas',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,428e3645-91fb-4239-8b52-b49a056eb2e7',
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
            name: 'product_name_starts_with',
            in: 'query',
            required: false,
            description: 'String for what the product name starts',
            example: 'Ju',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'product_name',
            in: 'query',
            required: false,
            description: 'Product name',
            example: 'Shop name',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Product\'s data',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product\'s data'),
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
            description: 'The product\' could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions|group_id|products_id|shops_id|product_name_starts_with, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ProductGetDataController extends AbstractController
{
    public function __construct(
        private ProductGetDataUseCase $productGetDataUseCase
    ) {
    }

    public function __invoke(ProductGetDataRequestDto $request): JsonResponse
    {
        $products = $this->productGetDataUseCase->__invoke(
            $this->createProductGetDataInputDto($request->groupId, $request->productsId, $request->shopsId, $request->productNameStartsWith, $request->productName)
        );

        return $this->createResponse($products);
    }

    private function createProductGetDataInputDto(string|null $groupId, array|null $productsId, array|null $shopsId, string|null $productNameStartsWith, string|null $productName): ProductGetDataInputDto
    {
        return new ProductGetDataInputDto($groupId, $productsId, $shopsId, $productNameStartsWith, $productName);
    }

    private function createResponse(ApplicationOutputInterface $products): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Products data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($products->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
