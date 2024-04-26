<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupRemove;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupRemove\Dto\GroupRemoveRequestDto;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use Group\Application\GroupRemove\GroupRemoveUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Group')]
#[OA\Delete(
    description: 'Removes a group',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'groups_id', type: 'array', description: 'Group\'s ids', items: new OA\Items()),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The group or groups has been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Group removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The group could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<groups_id_empty|group_id|group_not_found|permissions, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'The group could not be removed, not enough grants',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupRemoveController extends AbstractController
{
    public function __construct(
        private Security $security,
        private GroupRemoveUseCase $groupRemoveUseCase
    ) {
    }

    public function __invoke(GroupRemoveRequestDto $request): JsonResponse
    {
        $groupRemoved = $this->groupRemoveUseCase->__invoke(
            $this->createGroupRemoveInputDto($request->groupsId)
        );

        return $this->createResponse($groupRemoved);
    }

    /**
     * @param string[]|null $groupsId
     */
    private function createGroupRemoveInputDto(?array $groupsId): GroupRemoveInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new GroupRemoveInputDto($userSharedAdapter->getUser(), $groupsId);
    }

    private function createResponse(ApplicationOutputInterface $groupsRemovedId): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setData(['id' => $groupsRemovedId->toArray()])
            ->setMessage('Group removed')
            ->setStatus(RESPONSE_STATUS::OK);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
