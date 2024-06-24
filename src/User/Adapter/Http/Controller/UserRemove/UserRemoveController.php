<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRemove;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserRemove\Dto\UserRemoveRequestDto;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Application\UserRemove\Dto\UserRemoveInputDto;
use User\Application\UserRemove\UserRemoveUseCase;

#[OA\Tag('User')]
#[OA\Delete(
    description: 'Deletes an user from database',
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User Removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'User Removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'array', items: new OA\Items(type: 'string', example: 'bb1d3051-48b7-44d2-93d9-de58a2fcb5a8')
                                ),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You have not permissions to remove the user',
        ),
    ]
)]
class UserRemoveController extends AbstractController
{
    public function __construct(
        private UserRemoveUseCase $userRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(UserRemoveRequestDto $request): JsonResponse
    {
        $userRemovedId = $this->userRemoveUseCase->__invoke(
            $this->createUseRemoveInputDto()
        );

        return $this->createResponse($userRemovedId);
    }

    private function createUseRemoveInputDto(): UserRemoveInputDto
    {
        /** @var UserSymfonyAdapter $user */
        $user = $this->security->getUser();

        return new UserRemoveInputDto($user->getUser());
    }

    private function createResponse(ApplicationOutputInterface $userRemovedId): JsonResponse
    {
        $response = new ResponseDto($userRemovedId->toArray(), [], 'User Removed', RESPONSE_STATUS::OK);

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
