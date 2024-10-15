<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Security\jwt;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\Security\jwt\UserSharedSymfonyProviderAdapter;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\ResponseDto;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSharedSymfonyProviderAdapterTest extends TestCase
{
    /**
     * @var UserSharedSymfonyProviderAdapter<UserInterface>
     */
    private UserSharedSymfonyProviderAdapter $object;
    private MockObject&ModuleCommunicationInterface $moduleCommunication;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->object = new UserSharedSymfonyProviderAdapter($this->moduleCommunication);
    }

    /**
     * @return array{
     *  id: string,
     *  email: string,
     *  name: string,
     *  roles: string[],
     *  image: string|null,
     *  created_on: string
     * }
     */
    private function getUserData(Identifier $userId): array
    {
        return [
            'id' => $userId->getValue(),
            'email' => 'user@email.com',
            'name' => 'user name',
            'roles' => ['ROLE_USER'],
            'image' => null,
            'created_on' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }

    #[Test]
    public function itShouldLoadTheUserByIdentifier(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $userData = $this->getUserData($userId);
        $moduleCommunicationConfig = ModuleCommunicationFactory::userGet([$userId]);
        $responseDto = new ResponseDto([$userData]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfig)
            ->willReturn($responseDto);

        $return = $this->object->loadUserByIdentifier($userId->getValue());

        $this->assertEquals($userData['id'], $return->getUserIdentifier());
        $this->assertInstanceOf(UserSharedSymfonyAdapter::class, $return);
    }

    #[Test]
    public function itShouldFailLoadingTheUserByIdentifierError400(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $moduleCommunicationConfig = ModuleCommunicationFactory::userGet([$userId]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfig)
            ->willThrowException(new Error400Exception());

        $this->expectException(UserNotFoundException::class);
        $this->object->loadUserByIdentifier($userId->getValue());
    }

    #[Test]
    public function itShouldFailLoadingTheUserByIdentifierErrorModuleCommunication(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $moduleCommunicationConfig = ModuleCommunicationFactory::userGet([$userId]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfig)
            ->willThrowException(new ModuleCommunicationException());

        $this->expectException(UserNotFoundException::class);
        $this->object->loadUserByIdentifier($userId->getValue());
    }

    #[Test]
    public function itShouldFailLoadingTheUserByIdentifierErrorValueError(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $moduleCommunicationConfig = ModuleCommunicationFactory::userGet([$userId]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfig)
            ->willThrowException(new \ValueError());

        $this->expectException(UserNotFoundException::class);
        $this->object->loadUserByIdentifier($userId->getValue());
    }

    #[Test]
    public function itShouldRefreshTheUser(): void
    {
        $userSharedSymfonyAdapter = $this->createMock(UserSharedSymfonyAdapter::class);
        $return = $this->object->refreshUser($userSharedSymfonyAdapter);

        $this->assertSame($userSharedSymfonyAdapter, $return);
    }

    #[Test]
    public function itShouldFailRefreshTheUserUserIsNotAValidType(): void
    {
        $userSharedSymfonyAdapter = $this->createMock(UserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $this->object->refreshUser($userSharedSymfonyAdapter);
    }
}
