<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\GetUsers;

use Common\Domain\Response\ResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\GetUsers\Dto\GetUsersRequestDto;
use User\Application\GetUsers\Dto\GetUsersInputDto;
use User\Application\GetUsers\GetUsersService;

class GetUsersController extends AbstractController
{
    private GetUsersService $getUsersService;
    private int $getUsersMaxNumberUsersReturned;

    public function __construct(GetUsersService $getUsersService, int $getUsersMaxNumberUsersReturned)
    {
        $this->getUsersService = $getUsersService;
        $this->getUsersMaxNumberUsersReturned = $getUsersMaxNumberUsersReturned;
    }

    public function __invoke(GetUsersRequestDto $request): JsonResponse
    {
        $response = $this->getUsersService->__invoke(
            $this->createGetUsersInputDto($request->usersId)
        );

        return $this->createResponse($response->users);
    }

    private function createGetUsersInputDto(array|null $usersId): GetUsersInputDto
    {
        return new GetUsersInputDto($this->getUsersMaxNumberUsersReturned, $usersId);
    }

    private function createResponse(array $users): JsonResponse
    {
        $response = new ResponseDto(message: 'Users found', data: $users);

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
