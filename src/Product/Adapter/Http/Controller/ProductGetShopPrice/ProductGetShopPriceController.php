<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductGetShopPrice;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Product\Adapter\Http\Controller\ProductGetShopPrice\Dto\ProductGetShopPriceRequestDto;
use Product\Application\ProductGetShopPrice\Dto\ProductGetShopPriceInputDto;
use Product\Application\ProductGetShopPrice\ProductGetShopPriceUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Get(
    description: 'Get product\'s price',
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
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Product\'s price',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product\'s data'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'product_id', type: 'string'),
                                new OA\Property(property: 'shop_id', type: 'string'),
                                new OA\Property(property: 'price', type: 'float'),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The price of the product could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|products_id|shops_id|products_id_empty|shops_id_empty|permissions|products_not_found, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ProductGetShopPriceController extends AbstractController
{
    public function __construct(
        private ProductGetShopPriceUseCase $ProductGetShopPriceUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ProductGetShopPriceRequestDto $request): JsonResponse
    {
        $productsPrice = $this->ProductGetShopPriceUseCase->__invoke(
            $this->createProductGetShopPriceInputDto($request->productsId, $request->shopsId, $request->groupId)
        );

        return $this->createResponse($productsPrice);
    }

    private function createProductGetShopPriceInputDto(array|null $productsId, array|null $shopsId, string|null $groupId): ProductGetShopPriceInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ProductGetShopPriceInputDto($userSharedAdapter->getUser(), $productsId, $shopsId, $groupId);
    }

    private function createResponse(ApplicationOutputInterface $productsPrice): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Products prices')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($productsPrice->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
