<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserGetGroups;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupUserGetGroups\Dto\GroupUserGetGroupsRequestDto;
use Group\Application\GroupUserGetGroups\Dto\GroupUserGetGroupsInputDto;
use Group\Application\GroupUserGetGroups\GroupUserGetGroupsUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

#[OA\Tag('Group')]
#[OA\Get(
    description: 'Get user groups information',
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: true,
            description: 'Number of the page',
            example: '1',
            schema: new OA\Schema(type: 'integer')
        ),

        new OA\Parameter(
            name: 'page_items',
            in: 'query',
            required: false,
            description: 'Number of groups by page',
            example: 100,
            schema: new OA\Schema(type: 'int')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User groups found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Groups of the user'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'page', type: 'integer', example: '1', description: 'Number of the current page'),
                                new OA\Property(property: 'pages_total', type: 'integer', example: '5', description: 'Number of total pages'),
                                new OA\Property(property: 'groups', type: 'array', items: new OA\Items(
                                    properties: [
                                    new OA\Property(property: 'group_id', type: 'string', example: '1fcab788-0def-4e56-b441-935361678da9'),
                                    new OA\Property(property: 'name', type: 'string', example: 'GroupName'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Group description'),
                                    new OA\Property(property: 'image', type: 'string', example: 'Path to group Image'),
                                    new OA\Property(property: 'created_on', type: 'string', example: '2023-2-14 14:05:10'),
                                ])),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The user has not join any group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'No groups found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
    ]
)]
class GroupUserGetGroupsController extends AbstractController
{
    public function __construct(
        private Security $security,
        private GroupUserGetGroupsUseCase $groupUserGetGroupsUseCase
    ) {
    }

    public function __invoke(GroupUserGetGroupsRequestDto $request): JsonResponse
    {
        $userGroups = $this->groupUserGetGroupsUseCase->__invoke(
            $this->createGroupUserGetGroupsInputDto($request->page, $request->pageItems)
        );

        return $this->createResponse($userGroups);
    }

    private function createGroupUserGetGroupsInputDto(int $page, int $pgaItem): GroupUserGetGroupsInputDto
    {
        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupUserGetGroupsInputDto($userAdapter->getUser(), $page, $pgaItem);
    }

    private function createResponse(ApplicationOutputInterface $userGroups): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Groups of the user')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($userGroups->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
