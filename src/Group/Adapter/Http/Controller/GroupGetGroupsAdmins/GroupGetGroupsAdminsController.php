<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetGroupsAdmins;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupGetGroupsAdmins\Dto\GroupGetGroupsAdminsRequestDto;
use Group\Application\GroupGetGroupsAdmins\Dto\GroupGetGroupsAdminsInputDto;
use Group\Application\GroupGetGroupsAdmins\GroupGetGroupsAdminsUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Group')]
#[OA\Get(
    description: 'Get groups users id, that are administrators',
    parameters: [
        new OA\Parameter(
            name: 'groups_id',
            in: 'path',
            required: true,
            description: 'Groups id separated by comas',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,6a0bc88a-7474-47f7-a443-d7bcffd4b825',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'Number of the page',
            example: 1,
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: true,
            description: 'Number of groups per page',
            example: 100,
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Returns groups id and its admins user id',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Admins of the group'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'page', type: 'integer', example: '1', description: 'Number of the current page'),
                                new OA\Property(property: 'pages_total', type: 'integer', example: '5', description: 'Number of total pages'),
                                new OA\Property(property: 'groups', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: '<group id>', type: 'array', items: new OA\Items(
                                            example: '[2606508b-4516-45d6-93a6-c7cb416b7f3f,b11c9be1-b619-4ef5-be1b-a1cd9ef265b7]'
                                        )),
                                    ])),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Could not find admins of groups',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<groups_id|page|page_items, string>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'Groups is not found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_not_found, string>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupGetGroupsAdminsController extends AbstractController
{
    public function __construct(
        private GroupGetGroupsAdminsUseCase $groupGetGroupsAdminsUseCase,
        private Security $security
    ) {
    }

    public function __invoke(GroupGetGroupsAdminsRequestDto $request): JsonResponse
    {
        $groupAdmins = $this->groupGetGroupsAdminsUseCase->__invoke(
            $this->createGroupGetGroupsAdminsInputDto($request->groupsId, $request->page, $request->pageItems)
        );

        return $this->createResponse($groupAdmins);
    }

    /**
     * @param string[]|null $groupsId
     */
    private function createGroupGetGroupsAdminsInputDto(?array $groupsId, ?int $page, ?int $pageItems): GroupGetGroupsAdminsInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new GroupGetGroupsAdminsInputDto($userSharedAdapter->getUser(), $groupsId, $page, $pageItems);
    }

    private function createResponse(ApplicationOutputInterface $groupAdmins): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Admins of the groups')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($groupAdmins->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
