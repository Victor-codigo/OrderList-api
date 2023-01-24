<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupCreate;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupCreate\Dto\GroupCreateRequestDto;
use Group\Application\GroupCreate\Dto\GroupCreateInputDto;
use Group\Application\GroupCreate\GroupCreateUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

#[OA\Tag('Group')]
#[OA\Post(
    description: 'Creates a group',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, description: 'Group\'s name', example: 'GroupOne'),
                        new OA\Property(property: 'description', type: 'string', maxLength: VALUE_OBJECTS_CONSTRAINTS::DESCRIPTION_MAX_LENGTH, description: 'Grpup description', example: 'This is the description of the group'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The group has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Group created'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The group could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<name|description|group_name_repeated, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupCreateController extends AbstractController
{
    public function __construct(
        private GroupCreateUseCase $groupCreateUseCase,
        private Security $security
    ) {
    }

    public function __invoke(GroupCreateRequestDto $request): JsonResponse
    {
        $groupId = $this->groupCreateUseCase->__invoke(
            $this->createGroupCreateInputDto($request->name, $request->description)
        );

        return $this->createResponse($groupId);
    }

    private function createGroupCreateInputDto(string|null $name, string|null $description): GroupCreateInputDto
    {
        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupCreateInputDto(
            $userAdapter->getUser()->getId(),
            $name,
            $description
        );
    }

    private function createResponse(Identifier $groupId): JsonResponse
    {
        $responseDto = new ResponseDto(
            data: ['id' => $groupId->getValue()],
            message: 'Group created',
            status: RESPONSE_STATUS::OK
        );

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
