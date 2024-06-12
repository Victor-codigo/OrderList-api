<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserGetGroups;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupUserGetGroups\Dto\GroupUserGetGroupsRequestDto;
use Group\Application\GroupUserGetGroups\Dto\GroupUserGetGroupsInputDto;
use Group\Application\GroupUserGetGroups\GroupUserGetGroupsUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Group')]
#[OA\Get(
    description: 'Gets for user session, groups information',
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
            required: true,
            description: 'Number of groups by page',
            example: 100,
            schema: new OA\Schema(type: 'int')
        ),

        new OA\Parameter(
            name: 'group_type',
            in: 'query',
            required: false,
            description: 'Type of groups to return',
            example: 'user|group',
            schema: new OA\Schema(type: 'string')
        ),

        new OA\Parameter(
            name: 'filter_section',
            in: 'query',
            required: false,
            description: 'Section filter',
            example: 'group',
            schema: new OA\Schema(type: 'string')
        ),

        new OA\Parameter(
            name: 'filter_text',
            in: 'query',
            required: false,
            description: 'Text filter',
            example: 'equals',
            schema: new OA\Schema(type: 'string')
        ),

        new OA\Parameter(
            name: 'filter_value',
            in: 'query',
            required: false,
            description: 'Text to filter for',
            example: 'group',
            schema: new OA\Schema(type: 'string')
        ),

        new OA\Parameter(
            name: 'order_asc',
            in: 'query',
            required: true,
            description: 'Order ascending or descending',
            example: true,
            schema: new OA\Schema(type: 'boolean')
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
                                        new OA\Property(property: 'type', type: 'string', example: 'group|user'),
                                        new OA\Property(property: 'name', type: 'string', example: 'GroupName'),
                                        new OA\Property(property: 'description', type: 'string', example: 'Group description'),
                                        new OA\Property(property: 'image', type: 'string', example: 'Path to group Image'),
                                        new OA\Property(property: 'admin', type: 'boolean'),
                                        new OA\Property(property: 'created_on', type: 'string', example: '2023-2-14 14:05:10'),
                                    ])),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'The user has not join any group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema()
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The group could not be modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<filter_section_and_text_not_empty|section_filter_value|text_filter_value|page|page_items, string|array>')),
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
            $this->createGroupUserGetGroupsInputDto($request->page, $request->pageItems, $request->groupType, $request->filterSection, $request->filterText, $request->filterValue, $request->orderAsc)
        );

        return $this->createResponse($userGroups);
    }

    private function createGroupUserGetGroupsInputDto(int $page, int $pgaItem, ?string $groupType, ?string $filterSection, ?string $filterText, ?string $filterValue, bool $orderAsc): GroupUserGetGroupsInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new GroupUserGetGroupsInputDto($userSharedAdapter->getUser(), $page, $pgaItem, $groupType, $filterSection, $filterText, $filterValue, $orderAsc);
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
