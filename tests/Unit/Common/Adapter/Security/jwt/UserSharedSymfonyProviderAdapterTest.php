<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Security\jwt;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Adapter\Security\jwt\UserSharedSymfonyProviderAdapter;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\ResponseDto;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSharedSymfonyProviderAdapterTest extends TestCase
{
    private UserSharedSymfonyProviderAdapter $object;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|TokenExtractorInterface $tokenExtractor;
    private MockObject|RequestStack $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->tokenExtractor = $this->createMock(TokenExtractorInterface::class);
        $this->request = $this->createMock(RequestStack::class);
        $this->object = new UserSharedSymfonyProviderAdapter($this->moduleCommunication, $this->tokenExtractor, $this->request);
    }

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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function itShouldRefreshTheUser(): void
    {
        $userSharedSymfonyAdapter = $this->createMock(UserSharedSymfonyAdapter::class);
        $return = $this->object->refreshUser($userSharedSymfonyAdapter);

        $this->assertSame($userSharedSymfonyAdapter, $return);
    }

    /** @test */
    public function itShouldFailRefreshTheUserUserIsNotAValidType(): void
    {
        $userSharedSymfonyAdapter = $this->createMock(UserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $this->object->refreshUser($userSharedSymfonyAdapter);
    }
}
