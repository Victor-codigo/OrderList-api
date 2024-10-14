<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetDataByName;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupGetDataByName\Dto\GroupGetDataByNameRequestDto;
use Group\Application\GroupGetDataByName\Dto\GroupGetDataByNameInputDto;
use Group\Application\GroupGetDataByName\GroupGetDataByNameUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Group')]
#[OA\Get(
    description: 'Get group data by group name',
    parameters: [
        new OA\Parameter(
            name: 'group_name',
            in: 'path',
            required: true,
            description: 'The group name',
            example: 'GroupOne',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Group data',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Group data'),
                        new OA\Property(property: 'data', type: 'object',
                            properties: [
                                new OA\Property(property: 'group_id', type: 'string', example: '1fcab788-0def-4e56-b441-935361678da9'),
                                new OA\Property(property: 'type', type: 'string', example: 'group|user'),
                                new OA\Property(property: 'name', type: 'string', example: 'GroupName'),
                                new OA\Property(property: 'description', type: 'string', example: 'Group description'),
                                new OA\Property(property: 'image', type: 'string', example: 'Path to the group image'),
                                new OA\Property(property: 'created_on', type: 'string', example: '2023-2-14 14:05:10'),
                            ]),
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
                        new OA\Property(property: 'message', type: 'string', example: 'Group not found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_not_found, string>')),
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
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_name, string>')),
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
class GroupGetDataByNameController extends AbstractController
{
    public function __construct(
        private GroupGetDataByNameUseCase $GroupGetDataByNameUseCase,
        private Security $security,
    ) {
    }

    public function __invoke(GroupGetDataByNameRequestDto $request): JsonResponse
    {
        $groupData = $this->GroupGetDataByNameUseCase->__invoke(
            $this->createGroupGetDataByNameInputDto($request->groupName)
        );

        return $this->createResponse($groupData);
    }

    private function createGroupGetDataByNameInputDto(?string $groupName): GroupGetDataByNameInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new GroupGetDataByNameInputDto($userSharedAdapter->getUser(), $groupName);
    }

    private function createResponse(ApplicationOutputInterface $groupData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Group data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($groupData->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
