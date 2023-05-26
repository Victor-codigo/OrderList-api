<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductSetShopPrice;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Schema;
use Product\Adapter\Http\Controller\ProductSetShopPrice\Dto\ProductSetShopPriceRequestDto;
use Product\Application\ProductSetShopPrice\Dto\ProductSetShopPriceInputDto;
use Product\Application\ProductSetShopPrice\ProductSetShopPriceUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Patch(
    description: 'Sets the price of a product',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'product_id', type: 'string', description: 'Product\' id', example: '30c6dada-4f30-4fdc-9547-2682e4eb82ca'),
                        new OA\Property(property: 'shop_id', type: 'string', description: 'Shop\'s id', example: '140eae6d-5f40-44c4-8a50-d3f8f7825c5c'),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group\'s id', example: 'ff0a9cf0-e316-40a1-9a21-09cc716997a3'),
                        new OA\Property(property: 'price', type: 'float', description: 'Price to set to the product', example: '906ebffc-182a-42f1-abd6-30471a8fb58f'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Product\'s price changed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product created'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'product_id', type: 'string'),
                                new OA\Property(property: 'shop_id', type: 'string'),
                                new OA\Property(property: 'group_id', type: 'string'),
                                new OA\Property(property: 'price', type: 'string'),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Products\'s price cannot be changed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<product_id|shop_id|group_id|price|product_not_found|permissions, string>')),
                    ]
                )
            )
        ),
    ]
)]
class ProductSetShopPriceController extends AbstractController
{
    public function __construct(
        private ProductSetShopPriceUseCase $productSetShopPriceUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ProductSetShopPriceRequestDto $request): JsonResponse
    {
        $productShop = $this->productSetShopPriceUseCase->__invoke(
            $this->createShopProductSetPriceInputDto($request->productId, $request->shopId, $request->groupId, $request->price)
        );

        return $this->createResponse($productShop);
    }

    private function createShopProductSetPriceInputDto(string|null $productId, string|null $shopId, string|null $groupId, float|null $price): ProductSetShopPriceInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapterShared */
        $userAdapterShared = $this->security->getUser();

        return new ProductSetShopPriceInputDto($userAdapterShared->getUser(), $productId, $shopId, $groupId, $price);
    }

    private function createResponse(ApplicationOutputInterface $productShop): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Product of a shop set')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($productShop->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
