<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductRemoveAllGroupsProducts;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Ports\Security\UserSharedInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Product\Adapter\Http\Controller\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsRequestDto;
use Product\Application\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsInputDto;
use Product\Application\ProductRemoveAllGroupsProducts\ProductRemoveAllGroupsProductsUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Delete(
    description: 'Removes all products of groups passed',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'groups_id', type: 'array', description: 'Groups ids', items: new OA\Items()),
                        new OA\Property(property: 'system_key', type: 'string', description: 'Key of the system'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The products have been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Groups products removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The products could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<groups_id_empty|groups_id|system_key, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'Groups has no products',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Products not found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
    ]
)]
class ProductRemoveAllGroupsProductsController extends AbstractController
{
    public function __construct(
        private ProductRemoveAllGroupsProductsUseCase $productRemoveAllGroupsProductsUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ProductRemoveAllGroupsProductsRequestDto $request): JsonResponse
    {
        $productRemoved = $this->productRemoveAllGroupsProductsUseCase->__invoke(
            $this->createProductRemoveAllGroupsProductsInputDto($request->groupsId, $request->systemKey)
        );

        return $this->createResponse($productRemoved);
    }

    private function createProductRemoveAllGroupsProductsInputDto(?array $groupsId, ?string $systemKey): ProductRemoveAllGroupsProductsInputDto
    {
        /** @var UserSharedInterface $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ProductRemoveAllGroupsProductsInputDto($userSharedAdapter->getUser(), $groupsId, $systemKey);
    }

    private function createResponse(ApplicationOutputInterface $productRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Groups products removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($productRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
