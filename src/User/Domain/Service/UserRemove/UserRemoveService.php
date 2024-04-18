<?php

declare(strict_types=1);

namespace User\Domain\Service\UserRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\User\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\UserRemove\Dto\UserRemoveDto;

class UserRemoveService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private string $userImagePath
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(UserRemoveDto $userRemoveDto): void
    {
        $user = $this->userRepository->findUserByIdOrFail($userRemoveDto->userId);
        $this->removeUserImage($user);
        $this->removeUserData($user);

        $this->userRepository->save($user);
    }

    private function removeUserData(User $user): void
    {
        $user
            ->setRoles(ValueObjectFactory::createRoles([ValueObjectFactory::createRol(USER_ROLES::DELETED)]))
            ->setEmail(ValueObjectFactory::createEmail(''))
            ->setPassword(ValueObjectFactory::createPassword(''))
            ->setName(ValueObjectFactory::createNameWithSpaces(''));

        $profile = $user->getProfile();
        $profile
            ->setImage(ValueObjectFactory::createPath(null));
    }

    /**
     * @throws DomainInternalErrorException
     */
    private function removeUserImage(User $user): void
    {
        $image = $user->getProfile()->getImage()?->getValue();

        if (null === $image) {
            return;
        }

        $image = $this->userImagePath.'/'.$image;

        if (!file_exists($image)) {
            return;
        }

        $unlink = unlink($image);

        if (!$unlink) {
            throw DomainInternalErrorException::fromMessage('The image cannot be deleted');
        }
    }
}
