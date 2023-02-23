<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetData;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupGetData\Dto\GroupGetDataRequestDto;
use Group\Application\GroupGetData\Dto\GroupGetDataInputDto;
use Group\Application\GroupGetData\Dto\GroupGetDataOutputDto;
use Group\Application\GroupGetData\GroupGetDataUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

#[OA\Tag('Group')]
#[OA\Get(
    description: 'Get groups information',
    parameters: [
        new OA\Parameter(
            name: 'groups_id',
            in: 'path',
            required: true,
            description: 'a list of groups id separated by a coma',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,1fcab788-0def-4e56-b441-935361678da9',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Groups found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Groups data'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'group_id', type: 'string', example: '1fcab788-0def-4e56-b441-935361678da9'),
                                new OA\Property(property: 'name', type: 'string', example: 'GroupName'),
                                new OA\Property(property: 'description', type: 'string', example: 'Group description'),
                                new OA\Property(property: 'createdOn', type: 'string', example: '2023-2-14 14:05:10'),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'Not found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Not found: error 404'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Users not found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<groups_id, string>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You not belong to the group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions, string>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupGetDataController extends AbstractController
{
    public function __construct(
        private Security $security,
        private GroupGetDataUseCase $groupGetDataUseCase
    ) {
    }

    public function __invoke(GroupGetDataRequestDto $request): JsonResponse
    {
        $groupsData = $this->groupGetDataUseCase->__invoke(
            $this->createGroupGetDataInputDto($request->groupsId)
        );

        return $this->createResponse($groupsData);
    }

    private function createGroupGetDataInputDto(array|null $groupsId): GroupGetDataInputDto
    {
        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupGetDataInputDto($userAdapter->getUser(), $groupsId);
    }

    private function createResponse(GroupGetDataOutputDto $groupsData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setStatus(RESPONSE_STATUS::OK)
            ->setMessage('Groups data')
            ->setData(iterator_to_array($groupsData->data));

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
