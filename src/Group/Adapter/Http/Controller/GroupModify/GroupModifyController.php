<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupModify;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupModify\Dto\GroupModifyRequestDto;
use Group\Application\GroupModify\Dto\GroupModifyInputDto;
use Group\Application\GroupModify\GroupModifyUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Security\User\UserSymfonyAdapter;

#[OA\Tag('Group')]
#[OA\Put(
    description: 'Modify a group',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group\'s id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, description: 'Group\'s name', example: 'GroupOne'),
                        new OA\Property(property: 'description', type: 'string', maxLength: VALUE_OBJECTS_CONSTRAINTS::DESCRIPTION_MAX_LENGTH, description: 'Group description', example: 'This is the description of the group'),
                        new OA\Property(property: 'image_remove', type: 'boolean', description: 'TRUE if the group image is removed, FALSE no'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Group image'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The group has been modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Group modified'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
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
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|name|description|group_not_found|image, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You have not grants in this group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Not permissions in this group'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class GroupModifyController extends AbstractController
{
    public function __construct(
        private Security $security,
        private GroupModifyUseCase $groupModifyUseCase,
    ) {
    }

    public function __invoke(GroupModifyRequestDto $request): JsonResponse
    {
        $groupModified = $this->groupModifyUseCase->__invoke(
            $this->createGroupModifyInputDto($request)
        );

        return $this->createResponse($groupModified->groupId);
    }

    private function createGroupModifyInputDto(GroupModifyRequestDto $request): GroupModifyInputDto
    {
        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupModifyInputDto(
            $userAdapter->getUser(),
            $request->groupId,
            $request->name,
            $request->description,
            $request->imageRemove,
            null === $request->image ? null : new UploadedFileSymfonyAdapter($request->image)
        );
    }

    private function createResponse(Identifier $groupId): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Group modified')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData(['id' => $groupId->getValue()]);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
