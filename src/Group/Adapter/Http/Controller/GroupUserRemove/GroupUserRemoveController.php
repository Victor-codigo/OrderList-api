<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserRemove;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupUserRemove\Dto\GroupUserRemoveRequestDto;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveInputDto;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveOutputDto;
use Group\Application\GroupUserRemove\GroupUserRemoveUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Test\Unit\User\Adapter\Security\Jwt\Fixtures\UserAdapter;

#[OA\Tag('Group')]
#[OA\Delete(
    description: 'Removes users from a group',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'users', type: 'array', items: new OA\Items(), description: 'Ids of users to remove from the group', example: ['fdb242b4-bac8-4463-88d0-0941bb0beee0', '2606508b-4516-45d6-93a6-c7cb416b7f3f']),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Users has been deleted',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Users removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Users could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|users|group_without_admin|group_empty|group_users_not_found, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You have not grants to remove the users',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions, string>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupUserRemoveController extends AbstractController
{
    public function __construct(
        private Security $security,
        private GroupUserRemoveUseCase $groupUserRemoveUseCase
    ) {
    }

    public function __invoke(GroupUserRemoveRequestDto $request): JsonResponse
    {
        $groupUsersRemoveOutput = $this->groupUserRemoveUseCase->__invoke(
            $this->createGroupUserRemoveInputDto($request->groupId, $request->usersId)
        );

        return $this->createResponse($groupUsersRemoveOutput);
    }

    private function createGroupUserRemoveInputDto(string|null $groupId, array|null $usersId): GroupUserRemoveInputDto
    {
        /** @var UserAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupUserRemoveInputDto($userAdapter->getUser(), $groupId, $usersId);
    }

    private function createResponse(GroupUserRemoveOutputDto $groupUsersRemoveOutput): JsonResponse
    {
        $usersRemovedId = array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $groupUsersRemoveOutput->usersId
        );

        $responseDto = (new ResponseDto())
            ->setData(['id' => $usersRemovedId])
            ->setMessage('Users removed')
            ->setStatus(RESPONSE_STATUS::OK);

        return new JsonResponse($responseDto);
    }
}
