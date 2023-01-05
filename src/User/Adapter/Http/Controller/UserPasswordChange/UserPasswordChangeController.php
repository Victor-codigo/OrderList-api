<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordChange;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserPasswordChange\Dto\UserPasswordChangeRequestDto;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeInputDto;
use User\Application\UserPasswordChange\UserPasswordChangeUseCase;

#[OA\Tag('User')]
#[OA\Patch(
    description: 'Changes the user\'s password',
    requestBody: new OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'id', description: 'User\'s identifier', type: 'string', example: '2606508b-4516-45d6-93a6-c7cb416b7f3f'),
                    new OA\Property(property: 'passwordOld', description: 'User\'s current password', type: 'string', example: 'My current password'),
                    new OA\Property(property: 'passwordNew', description: 'User\'s new password', type: 'string', example: 'My new password'),
                    new OA\Property(property: 'passwordNewRepeat', description: 'User\'s new password repeated', type: 'string', default: 'My new password repeated'),
                ]
            )
        )]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Password changed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Password changed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Password cound not be changed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<password_change|password_new|password_new_repeat|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class UserPasswordChangeController extends AbstractController
{
    private UserPasswordChangeUseCase $userPasswordChangeUseCase;

    public function __construct(UserPasswordChangeUseCase $userPasswordChangeUseCase)
    {
        $this->userPasswordChangeUseCase = $userPasswordChangeUseCase;
    }

    public function __invoke(UserPasswordChangeRequestDto $passwordChangeRequestDto): JsonResponse
    {
        $this->userPasswordChangeUseCase->__invoke(
            $this->createUserPasswordChangeInputDto($passwordChangeRequestDto)
        );

        return $this->json(
            new ResponseDto(message: 'Password changed', status: RESPONSE_STATUS::OK),
            Response::HTTP_OK
        );
    }

    private function createUserPasswordChangeInputDto(UserPasswordChangeRequestDto $passwordChangeRequestDto): UserPasswordChangeInputDto
    {
        return new UserPasswordChangeInputDto(
            $passwordChangeRequestDto->id,
            $passwordChangeRequestDto->passwordOld,
            $passwordChangeRequestDto->passwordNew,
            $passwordChangeRequestDto->passwordNewRepeat
        );
    }
}
