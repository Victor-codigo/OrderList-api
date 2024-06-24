<?php

declare(strict_types=1);

namespace User\Domain\Service\UserRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\UserRemove\Dto\UserRemoveDto;

class UserRemoveService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EntityImageRemoveService $entityImageRemoveService,
        private string $userImagePath
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(UserRemoveDto $userRemoveDto): Identifier
    {
        $user = $this->userRepository->findUserByIdOrFail($userRemoveDto->userId);

        $this->entityImageRemoveService->__invoke($user, ValueObjectFactory::createPath($this->userImagePath));

        $this->userRepository->remove([$user]);

        return $user->getId();
    }
}
