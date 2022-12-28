<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserEmailChange;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Http\Controller\UserEmailChange\Dto\UserEmailChangeRequestDto;
use User\Application\UserEmailChange\Dto\UserEmailChangeInputDto;
use User\Application\UserEmailChange\UserEmailChangeService;

#[OA\Tag('User')]
#[OA\Patch(
    description: 'Changes the user email',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', required: ['true'], description: 'New user\'s email', example: 'Mary@hotmail.com'),
                new OA\Property(property: 'password', type: 'string', required: ['true'], description: 'The user\'s password', example: 'My password'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User\'s email has been modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Email modified'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'An errors has been occurred with the request',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'Error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Error'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<email|password|password_worng, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class UserEmailChangeController extends AbstractController
{
    public function __construct(
        private UserEmailChangeService $userEmailChangeService,
        private Security $security
    ) {
    }

    public function __invoke(UserEmailChangeRequestDto $request): JsonResponse
    {
        $this->userEmailChangeService->__invoke(
            $this->createUserChangeEmailInputDto($request)
        );

        $response = (new ResponseDto())
            ->setMessage('Email modified')
            ->setStatus(RESPONSE_STATUS::OK);

        return $this->json($response);
    }

    private function createUserChangeEmailInputDto(UserEmailChangeRequestDto $request): UserEmailChangeInputDto
    {
        return new UserEmailChangeInputDto(
            $this->security->getUser()->getUserIdentifier(),
            $request->email,
            $request->password
        );
    }
}
