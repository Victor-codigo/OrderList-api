<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersRemoveAllGroupsListsOrders;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersRequestDto;
use ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersInputDto;
use ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\ListOrdersRemoveAllGroupsListsOrdersUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Delete(
    description: 'Removes all lists of orders from passed groups or changes the user id',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'groups_id_remove', type: 'array', description: 'Groups id to remove listsOrders', items: new OA\Items(type: 'string', example: 'f916d316-d03b-416c-9d03-8d2bd3cee3b9')),
                        new OA\Property(property: 'groups_id_change_user_id', type: 'array', description: 'Groups id to change listsOrders user id', items: new OA\Items(type: 'string', example: '7cc20c53-7605-4e94-8df7-6df3ff8e013f')),
                        new OA\Property(property: 'system_key', type: 'string', description: 'System key'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The lists of orders has been removed or changed user id',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'listOrders removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                            new OA\Property(property: 'lists_orders_id_removed', type: 'array', items: new OA\Items(type: 'string', example: '0f68bb03-7a5c-49c9-b62d-1d8e38dc17d2')),
                            new OA\Property(property: 'lists_orders_id_user_changed', type: 'array', items: new OA\Items(type: 'string', example: 'f442f989-2dea-4377-bb47-7956fef26e99')),
                        ]
                        )),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The list of orders could not be removed or changed user id',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<groups_id_remove|groups_id_change_user_id|user_id_set|system_key, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersRemoveAllGroupsListsOrdersController extends AbstractController
{
    public function __construct(
        private ListOrdersRemoveAllGroupsListsOrdersUseCase $listOrdersRemoveAllGroupsListsOrdersUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersRemoveAllGroupsListsOrdersRequestDto $request): JsonResponse
    {
        $listsOrdersRemovedId = $this->listOrdersRemoveAllGroupsListsOrdersUseCase->__invoke(
            $this->createListOrdersRemoveAllGroupsListsOrdersInputDto(
                $request->groupsIdToRemove,
                $request->groupsIdToChangeUserId,
                $request->systemKey
            )
        );

        return $this->createResponse($listsOrdersRemovedId);
    }

    /**
     * @param string[]|null $listsOrdersIdToRemove
     * @param string[]|null $listsOrdersIdToChangeUserId
     */
    private function createListOrdersRemoveAllGroupsListsOrdersInputDto(?array $listsOrdersIdToRemove, ?array $listsOrdersIdToChangeUserId, ?string $systemKey): ListOrdersRemoveAllGroupsListsOrdersInputDto
    {
        /** @var UserSharedSymfonyAdapter $userShared */
        $userShared = $this->security->getUser();

        return new ListOrdersRemoveAllGroupsListsOrdersInputDto(
            $userShared->getUser(),
            $listsOrdersIdToRemove,
            $listsOrdersIdToChangeUserId,
            $systemKey
        );
    }

    private function createResponse(ApplicationOutputInterface $listsOrdersRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('lists of orders removed and changed user')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listsOrdersRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
