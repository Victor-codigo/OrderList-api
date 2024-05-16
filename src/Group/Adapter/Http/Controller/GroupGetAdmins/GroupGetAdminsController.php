<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetAdmins;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupGetAdmins\Dto\GroupGetAdminsRequestDto;
use Group\Application\GroupGetAdmins\Dto\GroupGetAdminsInputDto;
use Group\Application\GroupGetAdmins\Dto\GroupGetAdminsOutputDto;
use Group\Application\GroupGetAdmins\GroupGetAdminsUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Group')]
#[OA\Get(
    description: 'Get admins of groups',
    parameters: [
        new OA\Parameter(
            name: 'group_id',
            in: 'path',
            required: true,
            description: 'Group id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Groups admins ids',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Admins of the group'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'is_admin', type: 'string'),
                                new OA\Property(property: 'admins', type: 'array', items: new OA\Items()),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Could not find admins of the group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|group_not_found, string>')),
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
class GroupGetAdminsController extends AbstractController
{
    public function __construct(
        private GroupGetAdminsUseCase $GroupGetAdminsUseCase,
        private Security $security
    ) {
    }

    public function __invoke(GroupGetAdminsRequestDto $request): JsonResponse
    {
        $groupAdmins = $this->GroupGetAdminsUseCase->__invoke(
            $this->createGroupGetAdminsInputDto($request->groupId)
        );

        return $this->createResponse($groupAdmins);
    }

    private function createGroupGetAdminsInputDto(?string $groupId): GroupGetAdminsInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new GroupGetAdminsInputDto($userSharedAdapter->getUser(), $groupId);
    }

    private function createResponse(GroupGetAdminsOutputDto $groupAdmins): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Admins of the group')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($groupAdmins->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
