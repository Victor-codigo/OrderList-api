<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupRemoveAllUserGroups;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsRequestDto;
use Group\Application\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsInputDto;
use Group\Application\GroupRemoveAllUserGroups\GroupRemoveAllUserGroupsUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Group')]
#[OA\Delete(
    description: 'Removes the session user from all groups. Removes all groups that user administrate and is the unique user. In groups that user is the unique administrator, removes the user and set another administrator for the group',

    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'Groups removed, groups in which user has been removed, and groups that has changed administrator',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'User removed from all groups'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'groups_id_removed', type: 'array', items: new OA\Items()),
                                new OA\Property(property: 'groups_id_user_removed', type: 'array', items: new OA\Items()),
                                new OA\Property(property: 'groups_id_user_set_as_admin', type: 'array', items: new OA\Items()),
                            ]
                        )),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The user groups could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_not_found|internal_error_server, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupRemoveAllUserGroupsController extends AbstractController
{
    public function __construct(
        private Security $security,
        private GroupRemoveAllUserGroupsUseCase $groupRemoveAllUserGroupsUseCase
    ) {
    }

    public function __invoke(GroupRemoveAllUserGroupsRequestDto $request): JsonResponse
    {
        $groupRemoved = $this->groupRemoveAllUserGroupsUseCase->__invoke(
            $this->createGroupRemoveInputDto()
        );

        return $this->createResponse($groupRemoved);
    }

    private function createGroupRemoveInputDto(): GroupRemoveAllUserGroupsInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new GroupRemoveAllUserGroupsInputDto($userSharedAdapter->getUser());
    }

    private function createResponse(ApplicationOutputInterface $groupsRemovedId): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setData($groupsRemovedId->toArray())
            ->setMessage('User removed from all groups')
            ->setStatus(RESPONSE_STATUS::OK);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
