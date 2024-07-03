<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductRemove;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Ports\Security\UserSharedInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Items;
use Product\Adapter\Http\Controller\ProductRemove\Dto\ProductRemoveRequestDto;
use Product\Application\ProductRemove\Dto\ProductRemoveInputDto;
use Product\Application\ProductRemove\ProductRemoveUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Delete(
    description: 'Removes a product',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group\'s id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'products_id', type: 'array', description: 'Product\'s ids', example: '[fdb242b4-bac8-4463-88d0-0941bb0beee0]', items: new Items('string')),
                        new OA\Property(property: 'shops_id', type: 'array', description: 'Shop\'s ids', example: '[fdb242b4-bac8-4463-88d0-0941bb0beee0]', items: new Items('string')),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The product has been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product removed'),
                        new OA\Property(property: 'data', type: 'array', items: new Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The product could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new Items(default: '<group_id|product_id|shop_id|product_not_found|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ProductRemoveController extends AbstractController
{
    public function __construct(
        private ProductRemoveUseCase $ProductRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ProductRemoveRequestDto $request): JsonResponse
    {
        $productRemoved = $this->ProductRemoveUseCase->__invoke(
            $this->createProductRemoveInputDto($request->groupId, $request->productsId, $request->shopsId)
        );

        return $this->createResponse($productRemoved);
    }

    /**
     * @param string[]|null $productsId
     * @param string[]|null $shopsId    $name
     */
    private function createProductRemoveInputDto(?string $groupId, ?array $productsId, ?array $shopsId): ProductRemoveInputDto
    {
        /** @var UserSharedInterface $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ProductRemoveInputDto($userSharedAdapter->getUser(), $groupId, $productsId, $shopsId);
    }

    private function createResponse(ApplicationOutputInterface $productRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Product removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($productRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
