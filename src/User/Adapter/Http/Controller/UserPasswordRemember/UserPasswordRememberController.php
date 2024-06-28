<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRemember;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserPasswordRemember\Dto\UserPasswordRememberRequestDto;
use User\Application\UserPasswordRemember\Dto\UserPasswordRememberInputDto;
use User\Application\UserPasswordRemember\UserPasswordRememberUseCase;

#[OA\Tag('User')]
#[OA\Post(
    description: 'Remember the user\'s password',
    requestBody: new OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'email', type: 'string', description: 'User\'s email', example: 'mary@hotmail.com'),
                    new OA\Property(property: 'email_password_remember_url', type: 'boolean', description: 'URL where the email owner is confirmated', example: 'http://domain.com/user/email/comifrmation'),
                ]
            )
        )]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Request accepted',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Request accepted'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Invalid parameters',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid parameters'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<email|passwordRememberUrl, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class UserPasswordRememberController extends AbstractController
{
    private UserPasswordRememberUseCase $userPasswordRememberUseCase;

    public function __construct(UserPasswordRememberUseCase $userPasswordRememberUseCase)
    {
        $this->userPasswordRememberUseCase = $userPasswordRememberUseCase;
    }

    public function __invoke(UserPasswordRememberRequestDto $passwordRememberDto): JsonResponse
    {
        $this->userPasswordRememberUseCase->__invoke(
            $this->createUserPasswordRememberInputDto($passwordRememberDto->email, $passwordRememberDto->passwordRememberUrl)
        );

        return $this->json($this->createResponseDto(), Response::HTTP_OK);
    }

    private function createUserPasswordRememberInputDto(?string $email, ?string $passwordRememberUrl): UserPasswordRememberInputDto
    {
        return new UserPasswordRememberInputDto($email, $passwordRememberUrl);
    }

    private function createResponseDto(): ResponseDto
    {
        return new ResponseDto(message: 'Request accepted', status: RESPONSE_STATUS::OK);
    }
}
