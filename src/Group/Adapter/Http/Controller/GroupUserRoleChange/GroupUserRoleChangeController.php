<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserRoleChange;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Security\UserSharedInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupUserRoleChange\Dto\GroupUserRoleChangeRequestDto;
use Group\Application\GroupUserRoleChange\Dto\GroupUserRoleChangeInputDto;
use Group\Application\GroupUserRoleChange\GroupUserRoleChangeUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Group')]
#[OA\Put(
    description: 'Changes rol users\'s group',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'users', type: 'array', items: new OA\Items(), description: 'Ids of users of the group, to change rol', example: ['fdb242b4-bac8-4463-88d0-0941bb0beee0', '2606508b-4516-45d6-93a6-c7cb416b7f3f']),
                        new OA\Property(property: 'admin', type: 'boolean', description: 'TRUE to change rol to admin, FALSE to change rol to user', example: true),
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
            description: 'Users rol in the group, could not be changed',
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
            response: Response::HTTP_CONFLICT,
            description: 'The group must have at least one administrator',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'It should be at least one admin in the group'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_not_admins, string>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'The user is not admin in the group',
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
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<users_not_found, string>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupUserRoleChangeController extends AbstractController
{
    public function __construct(
        private GroupUserRoleChangeUseCase $groupUserRoleChangeUseCase,
        private Security $security
    ) {
    }

    public function __invoke(GroupUserRoleChangeRequestDto $request): JsonResponse
    {
        $usersModified = $this->groupUserRoleChangeUseCase->__invoke(
            $this->createGroupUserRoleChangeInputDto($request->groupId, $request->usersId, $request->admin)
        );

        return $this->createResponse($usersModified->usersModifiedIds);
    }

    /**
     * @param string[]|null $usersId
     */
    private function createGroupUserRoleChangeInputDto(string|null $groupId, array|null $usersId, bool|null $admin): GroupUserRoleChangeInputDto
    {
        /** @var UserSharedInterface $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new GroupUserRoleChangeInputDto($userSharedAdapter->getUser(), $groupId, $usersId, $admin);
    }

    /**
     * @param Identifier[] $usersId
     */
    private function createResponse(array $usersId): JsonResponse
    {
        $resposeData = new ResponseDto(
            ['id' => array_map(fn (Identifier $id) => $id->getValue(), $usersId)],
            [],
            'Users roles has been changed',
            RESPONSE_STATUS::OK
        );

        return new JsonResponse($resposeData, Response::HTTP_OK);
    }
}
