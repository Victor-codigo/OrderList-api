<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserAdd;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupUserAdd\Dto\GroupUserAddRequestDto;
use Group\Application\GroupUserAdd\Dto\GroupUserAddInputDto;
use Group\Application\GroupUserAdd\GroupUserAddUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

class GroupUserAddController extends AbstractController
{
    public function __construct(
        private GroupUserAddUseCase $groupUserAddUseCase,
        private Security $security
    ) {
    }

    public function __invoke(GroupUserAddRequestDto $request): JsonResponse
    {
        $usersModifiedId = $this->groupUserAddUseCase->__invoke(
            $this->createGrouUserAddInputDto($request->groupId, $request->usersId, $request->admin)
        );

        return $this->createResponse($usersModifiedId->usersId);
    }

    private function createGrouUserAddInputDto(string|null $groupId, array|null $usersId, bool|null $admin): GroupUserAddInputDto
    {
        /** @var UserSymfonyAdapter */
        $userAdapter = $this->security->getUser();

        return new GroupUserAddInputDto(
            $userAdapter->getUser(),
            $groupId,
            $usersId,
            $admin
        );
    }

    /**
     * @param Identifier[] $usersId
     */
    private function createResponse(array $usersId): JsonResponse
    {
        $users = array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $usersId
        );

        $responseData = new ResponseDto(
            ['id' => $users],
            [],
            'Users added to the group',
            RESPONSE_STATUS::OK
        );

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}
