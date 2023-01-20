<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupCreate;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Group\Adapter\Http\Controller\GroupCreate\Dto\GroupCreateRequestDto;
use Group\Application\GroupCreate\Dto\GroupCreateInputDto;
use Group\Application\GroupCreate\GroupCreateUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Security\User\UserSymfonyAdapter;

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

    private function createGroupCreateInputDto(string $name, string $description): GroupCreateInputDto
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
