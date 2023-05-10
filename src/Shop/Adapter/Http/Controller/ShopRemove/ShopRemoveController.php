<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopRemove;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Ports\Security\UserSharedInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Shop\Adapter\Http\Controller\ShopRemove\Dto\ShopRemoveRequestDto;
use Shop\Application\ShopRemove\Dto\ShopRemoveInputDto;
use Shop\Application\ShopRemove\ShopRemoveUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Shop')]
#[OA\Delete(
    description: 'Removes a shop',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group\'s id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'shop_id', type: 'string', description: 'Shop\'s id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'product_id', type: 'string', description: 'Product\'s id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The shop has been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Shop removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The shop could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|shop_id|shop_id|shop_not_found|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ShopRemoveController extends AbstractController
{
    public function __construct(
        private ShopRemoveUseCase $shopRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ShopRemoveRequestDto $request): JsonResponse
    {
        $shopRemoved = $this->shopRemoveUseCase->__invoke(
            $this->createShopRemoveInputDto($request->groupId, $request->shopId, $request->productId)
        );

        return $this->createResponse($shopRemoved);
    }

    private function createShopRemoveInputDto(string|null $groupId, string|null $shopId, string|null $productId): ShopRemoveInputDto
    {
        /** @var UserSharedInterface $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ShopRemoveInputDto($userSharedAdapter->getUser(), $groupId, $shopId, $productId);
    }

    private function createResponse(ApplicationOutputInterface $shopRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Shop removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($shopRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
