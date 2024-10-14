<?php

declare(strict_types=1);

namespace Common\Adapter\Security\jwt;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\User\USER_ROLES;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @template-covariant TUser of UserInterface
 *
 * @implements UserProviderInterface<UserInterface>
 */
class UserSharedSymfonyProviderAdapter implements UserProviderInterface
{
    public function __construct(
        private ModuleCommunicationInterface $moduleCommunication,
    ) {
    }

    /**
     * @throws UnsupportedUserException if the user is not supported
     */
    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserSharedSymfonyAdapter) {
            throw new UnsupportedUserException(sprintf('It is not an instance of %s', UserSharedSymfonyAdapter::class));
        }

        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     */
    #[\Override]
    public function supportsClass(string $class): bool
    {
        return UserSharedSymfonyAdapter::class === $class;
    }

    /**
     * @throws UserNotFoundException
     */
    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            $userId = ValueObjectFactory::createIdentifier($identifier);

            /**
             * @var object{
             *  data: array{}|array<int, array{
             *      id: string,
             *      email: string,
             *      name: string,
             *      roles: string[],
             *      created_on: string,
             *      image: string|null
             * }>} $response
             */
            $response = $this->moduleCommunication->__invoke(
                ModuleCommunicationFactory::userGet([$userId])
            );

            return $this->createUserSharedSymfonyAdapter($response->data[0]);
        } catch (Error400Exception|ModuleCommunicationException|\ValueError) {
            throw new UserNotFoundException('Identifier not found: ', $identifier);
        }
    }

    /**
     * @param array{}|array{
     *  id: string,
     *  email: string,
     *  name: string,
     *  roles: string[],
     *  created_on: string,
     *  image: string|null
     * } $userData
     */
    private function createUserSharedSymfonyAdapter(array $userData): UserSharedSymfonyAdapter
    {
        $roles = array_map(
            fn (string $rolPlain): ?USER_ROLES => USER_ROLES::tryFrom($rolPlain),
            $userData['roles']
        );

        $userShared = UserShared::fromPrimitives(
            $userData['id'],
            $userData['email'],
            $userData['name'],
            $roles,
            $userData['image'],
            new \DateTime($userData['created_on'])
        );

        return new UserSharedSymfonyAdapter($userShared);
    }
}
