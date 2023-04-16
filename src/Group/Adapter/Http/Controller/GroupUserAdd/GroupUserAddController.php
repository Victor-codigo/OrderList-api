<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserAdd;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupUserAdd\Dto\GroupUserAddRequestDto;
use Group\Application\GroupUserAdd\Dto\GroupUserAddInputDto;
use Group\Application\GroupUserAdd\GroupUserAddUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

#[OA\Tag('Group')]
#[OA\Post(
    description: 'Adds users to a group',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'users', type: 'array', items: new OA\Items(), description: 'Ids of users to add to the group', example: ['fdb242b4-bac8-4463-88d0-0941bb0beee0', '2606508b-4516-45d6-93a6-c7cb416b7f3f']),
                        new OA\Property(property: 'admin', type: 'boolean', description: 'Users\'s role in the group. TRUE to set rol to admin, FALSE to set rol to user', example: false),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Users has changed their rol in the group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Users roles has been changed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Users could not be added',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|users, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You have not grants to add the users',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permission, string>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'The group could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_not_found, string>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupUserAddController extends AbstractController
{
    public function __construct(
        private GroupUserAddUseCase $groupUserAddUseCase,
        private Security $security
    ) {
    }

    public function __invoke(GroupUserAddRequestDto $request): JsonResponse
    {
        $usersModifiedId = $this->groupUserAddUseCase->__invoke(
            $this->createGroupUserAddInputDto($request->groupId, $request->users, $request->identifierType, $request->admin)
        );

        return $this->createResponse($usersModifiedId->usersId);
    }

    private function createGroupUserAddInputDto(string|null $groupId, array|null $users, string|null $identifierType, bool|null $admin): GroupUserAddInputDto
    {
        /** @var UserSymfonyAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupUserAddInputDto(
            $userAdapter->getUser(),
            $groupId,
            $users,
            $identifierType,
            $admin
        );
    }

    /**
     * @param Identifier[] $usersId
     */
    private function createResponse(array $usersId): JsonResponse
    {
        $users = array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $usersId
        );

        $responseData = new ResponseDto(
            ['id' => $users],
            [],
            'Users added to the group',
            RESPONSE_STATUS::OK
        );

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}
