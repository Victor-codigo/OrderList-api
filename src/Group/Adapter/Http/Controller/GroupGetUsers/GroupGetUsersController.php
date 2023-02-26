<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetUsers;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupGetUsers\Dto\GroupGetUsersRequestDto;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersInputDto;
use Group\Application\GroupGetUsers\GroupGetUsersUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

class GroupGetUsersController extends AbstractController
{
    public function __construct(
        private Security $security,
        private GroupGetUsersUseCase $groupGetUsersUseCase
    ) {
    }

    public function __invoke(GroupGetUsersRequestDto $request): JsonResponse
    {
        $groupUsers = $this->groupGetUsersUseCase->__invoke(
            $this->createGroupGetUsersInputDto($request->groupId, $request->limit, $request->offset)
        );

        return $this->createResponse($groupUsers->users);
    }

    private function createGroupGetUsersInputDto(string|null $groupId, int $start, int $offset): GroupGetUsersInputDto
    {
        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupGetUsersInputDto($userAdapter->getUser(), $groupId, $start, $offset);
    }

    private function createResponse(array $groupUsers): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Users of the group')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($groupUsers);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
