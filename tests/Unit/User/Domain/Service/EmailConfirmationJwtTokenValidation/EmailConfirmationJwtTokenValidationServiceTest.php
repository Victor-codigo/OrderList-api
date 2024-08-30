<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\EmailConfirmationJwtTokenValidation;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Jwt\Exception\JwtTokenExpiredException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\Dto\EmailConfirmationJwtTokenValidationDto;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\EmailConfirmationJwtTokenValidationService;

class EmailConfirmationJwtTokenValidationServiceTest extends TestCase
{
    private EmailConfirmationJwtTokenValidationService $object;
    private MockObject|JwtHS256Interface $jwt;
    private MockObject|UserRepositoryInterface $userRepository;
    private MockObject|User $user;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->jwt = $this->createMock(JwtHS256Interface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->user = $this->createMock(User::class);
        $this->object = new EmailConfirmationJwtTokenValidationService($this->jwt, $this->userRepository);
    }

    private function createTokenClass(): object
    {
        return new class() {
            public string $username = 'user id';
        };
    }

    #[Test]
    public function itShouldFailTokenHasExpired(): void
    {
        $token = ValueObjectFactory::createJwtToken('token');
        $tokenDecoded = $this->createTokenClass();
        $input = new EmailConfirmationJwtTokenValidationDto($token);

        $this->jwt
            ->expects($this->once())
            ->method('decode')
            ->with($token->getValue())
            ->willReturn($tokenDecoded);

        $this->jwt
            ->expects($this->once())
            ->method('hasExpired')
            ->with($tokenDecoded)
            ->willReturn(true);

        $this->expectException(JwtTokenExpiredException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailUserIdNotFound(): void
    {
        $token = ValueObjectFactory::createJwtToken('token');
        $tokenDecoded = $this->createTokenClass();
        $input = new EmailConfirmationJwtTokenValidationDto($token);

        $this->jwt
            ->expects($this->once())
            ->method('decode')
            ->with($token->getValue())
            ->willReturn($tokenDecoded);

        $this->jwt
            ->expects($this->once())
            ->method('hasExpired')
            ->with($tokenDecoded)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($tokenDecoded->username)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailUserHasNotRolNotActive(): void
    {
        $token = ValueObjectFactory::createJwtToken('token');
        $tokenDecoded = $this->createTokenClass();
        $input = new EmailConfirmationJwtTokenValidationDto($token);
        $roles = ValueObjectFactory::createRoles([
            ValueObjectFactory::createRol(USER_ROLES::USER),
        ]);

        $this->jwt
            ->expects($this->once())
            ->method('decode')
            ->with($token->getValue())
            ->willReturn($tokenDecoded);

        $this->jwt
            ->expects($this->once())
            ->method('hasExpired')
            ->with($tokenDecoded)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($tokenDecoded->username)
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles);

        $this->expectException(InvalidArgumentException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailErrorDatabaseConnection(): void
    {
        $token = ValueObjectFactory::createJwtToken('token');
        $tokenDecoded = $this->createTokenClass();
        $input = new EmailConfirmationJwtTokenValidationDto($token);
        $roles = ValueObjectFactory::createRoles([
            ValueObjectFactory::createRol(USER_ROLES::NOT_ACTIVE),
        ]);

        $this->jwt
            ->expects($this->once())
            ->method('decode')
            ->with($token->getValue())
            ->willReturn($tokenDecoded);

        $this->jwt
            ->expects($this->once())
            ->method('hasExpired')
            ->with($tokenDecoded)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($tokenDecoded->username)
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles);

        $this->user
            ->expects($this->once())
            ->method('setRoles')
            ->with(Roles::create([USER_ROLES::USER_FIRST_LOGIN]));

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->user)
            ->willThrowException(new DBConnectionException());

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldActivateTheUser(): void
    {
        $token = ValueObjectFactory::createJwtToken('token');
        $tokenDecoded = $this->createTokenClass();
        $input = new EmailConfirmationJwtTokenValidationDto($token);
        $roles = ValueObjectFactory::createRoles([
            ValueObjectFactory::createRol(USER_ROLES::NOT_ACTIVE),
        ]);

        $this->jwt
            ->expects($this->once())
            ->method('decode')
            ->with($token->getValue())
            ->willReturn($tokenDecoded);

        $this->jwt
            ->expects($this->once())
            ->method('hasExpired')
            ->with($tokenDecoded)
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findUserByIdOrFail')
            ->with($tokenDecoded->username)
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn($roles);

        $this->user
            ->expects($this->once())
            ->method('setRoles')
            ->with(Roles::create([USER_ROLES::USER_FIRST_LOGIN]));

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->user);

        $this->object->__invoke($input);
    }
}
