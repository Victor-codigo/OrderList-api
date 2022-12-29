<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserModify;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Http\Controller\UserModify\Dto\UserModifyRequestDto;
use User\Application\UserModify\Dto\UserModifyInputDto;
use User\Application\UserModify\UserModifyService;
use User\Domain\Port\User\UserInterface;

#[OA\Tag('User')]
#[OA\Put(description: 'This is the one to be use instead of POST version. The structure is the same as POST, but for documentation purposes POST version exists ')]
#[OA\Post(
    description: 'Modify current user. (Use PUT version)',
    requestBody: new OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'name', description: 'User\'s name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, example: 'Mary'),
                    new OA\Property(property: 'image_remove', description: 'TRUE: user\'s image is removed. FALSE: do nothing', type: 'boolean', example: false),
                    new OA\Property(property: 'image', description: 'User\'s image', type: 'file', format: 'binary'),
                    new OA\Property(property: '_method', description: 'Indicates the html verb', type: 'string', default: 'PUT'),
                ]
            )
        )]
    ),

    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'User modified'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Error',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<name|image, string>')),
                    ]
                )
            )
        ),
    ]
)]
class UserModifyController extends AbstractController
{
    public function __construct(
        private UserModifyService $userModifyService,
        private Security $security
    ) {
    }

    public function __invoke(UserModifyRequestDto $request): JsonResponse
    {
        $this->userModifyService->__invoke(
            $this->createUserModifyInputDto($request)
        );

        $response = (new ResponseDto())
            ->setMessage('User modified')
            ->setStatus(RESPONSE_STATUS::OK);

        return $this->json($response);
    }

    private function createUserModifyInputDto(UserModifyRequestDto $requestDto): UserModifyInputDto
    {
        /** @var UserInterface */
        $user = $this->security->getUser();

        return UserModifyInputDto::create(
            $this->security->getUser()->getUserIdentifier(),
            $requestDto->name,
            $requestDto->imageRemove,
            null === $requestDto->image ? null : new UploadedFileSymfonyAdapter($requestDto->image),
            $user->getUser()
        );
    }
}
