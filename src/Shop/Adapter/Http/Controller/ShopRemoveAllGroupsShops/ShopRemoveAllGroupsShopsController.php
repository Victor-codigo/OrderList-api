<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopRemoveAllGroupsShops;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Ports\Security\UserSharedInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Shop\Adapter\Http\Controller\ShopRemoveAllGroupsShops\Dto\ShopRemoveAllGroupsShopsRequestDto;
use Shop\Application\ShopRemoveAllGroupsShops\Dto\ShopRemoveAllGroupsShopsInputDto;
use Shop\Application\ShopRemoveAllGroupsShops\ShopRemoveAllGroupsShopsUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Shop')]
#[OA\Delete(
    description: 'Removes all shops of groups passed',
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
            description: 'The shops have been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Groups shops removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The shops could not be removed',
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
            description: 'Groups has no shops',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Shops not found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
    ]
)]
class ShopRemoveAllGroupsShopsController extends AbstractController
{
    public function __construct(
        private ShopRemoveAllGroupsShopsUseCase $shopRemoveAllGroupsShopsUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ShopRemoveAllGroupsShopsRequestDto $request): JsonResponse
    {
        $shopRemoved = $this->shopRemoveAllGroupsShopsUseCase->__invoke(
            $this->createShopRemoveAllGroupsShopsInputDto($request->groupsId, $request->systemKey)
        );

        return $this->createResponse($shopRemoved);
    }

    private function createShopRemoveAllGroupsShopsInputDto(?array $groupsId, ?string $systemKey): ShopRemoveAllGroupsShopsInputDto
    {
        /** @var UserSharedInterface $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ShopRemoveAllGroupsShopsInputDto($userSharedAdapter->getUser(), $groupsId, $systemKey);
    }

    private function createResponse(ApplicationOutputInterface $shopRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Groups shops removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($shopRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
