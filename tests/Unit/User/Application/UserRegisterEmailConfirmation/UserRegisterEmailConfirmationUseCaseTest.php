<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserRegisterEmailConfirmation;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Adapter\Jwt\Exception\JwtTokenExpiredException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Event\EventDomainInterface;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Event\EventDispatcherInterface;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Application\UserRegisterEmailConfirmation\Dto\UserEmailConfirmationInputDto;
use User\Application\UserRegisterEmailConfirmation\Dto\UserEmailConfirmationOutputDto;
use User\Application\UserRegisterEmailConfirmation\Exception\EmailConfigurationJwtTokenHasExpiredException;
use User\Application\UserRegisterEmailConfirmation\Exception\EmailConfirmationJwtTokenNotValidException;
use User\Application\UserRegisterEmailConfirmation\Exception\EmailConfirmationUserAlreadyActiveException;
use User\Application\UserRegisterEmailConfirmation\UserRegisterEmailConfirmationUseCase;
use User\Domain\Model\User;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\Dto\EmailConfirmationJwtTokenValidationDto;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\EmailConfirmationJwtTokenValidationService;

class UserRegisterEmailConfirmationUseCaseTest extends TestCase
{
    private UserRegisterEmailConfirmationUseCase $object;
    private MockObject|ValidationInterface $validator;
    private MockObject|EmailConfirmationJwtTokenValidationService $emailConfirmationJwtTokenValidation;
    private MockObject|EventDispatcherInterface $eventDispatcherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(ValidationInterface::class);
        $this->emailConfirmationJwtTokenValidation = $this->createMock(EmailConfirmationJwtTokenValidationService::class);
        $this->eventDispatcherService = $this->createMock(EventDispatcherInterface::class);

        $this->object = new UserRegisterEmailConfirmationUseCase(
            $this->validator,
            $this->emailConfirmationJwtTokenValidation,
            $this->eventDispatcherService
        );
    }

    /** @test */
    public function itShouldConfirmUserRegistration()
    {
        $token = 'token valid';
        $userId = ValueObjectFactory::createIdentifier('user id');
        /** @var MockObject|UserEmailConfirmationInputDto $input */
        $input = $this->getMockBuilder(UserEmailConfirmationInputDto::class)
            ->setConstructorArgs([$token])
            ->onlyMethods(['validate'])
            ->getMock();

        /** @var MockObject|User $userExpected */
        $userExpected = $this->createMock(User::class);
        $eventExpected = $this->createMock(EventDomainInterface::class);

        $input
            ->expects($this->once())
            ->method('validate')
            ->with($this->validator)
            ->willReturn([]);

        $this->emailConfirmationJwtTokenValidation
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (EmailConfirmationJwtTokenValidationDto $input) use ($token) {
                $this->assertEquals(ValueObjectFactory::createJwtToken($token), $input->token);

                return true;
            }))
            ->willReturn($userExpected);

        $this->eventDispatcherService
            ->expects($this->once())
            ->method('dispatch')
            ->with($eventExpected);

        $userExpected
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $userExpected
            ->expects($this->once())
            ->method('getEventsRegistered')
            ->willReturn([$eventExpected]);

        $return = $this->object->__invoke($input);

        $this->assertInstanceOf(UserEmailConfirmationOutputDto::class, $return);
        $this->assertEquals($userId, $return->id);
    }

    /** @test */
    public function itShouldFailInputValidationError()
    {
        $token = 'token valid';
        /** @var MockObject|UserEmailConfirmationInputDto $input */
        $input = $this->getMockBuilder(UserEmailConfirmationInputDto::class)
            ->setConstructorArgs([$token])
            ->onlyMethods(['validate'])
            ->getMock();

        $input
            ->expects($this->once())
            ->method('validate')
            ->with($this->validator)
            ->willReturn(['field' => [VALIDATION_ERRORS::ALPHANUMERIC]]);

        $this->expectException(ValueObjectValidationException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailTokenExpired()
    {
        $token = 'token expired';
        /** @var MockObject|UserEmailConfirmationInputDto $input */
        $input = $this->getMockBuilder(UserEmailConfirmationInputDto::class)
            ->setConstructorArgs([$token])
            ->onlyMethods(['validate'])
            ->getMock();

        $input
            ->expects($this->once())
            ->method('validate')
            ->with($this->validator)
            ->willReturn([]);

        $this->emailConfirmationJwtTokenValidation
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (EmailConfirmationJwtTokenValidationDto $input) use ($token) {
                $this->assertEquals(ValueObjectFactory::createJwtToken($token), $input->token);

                return true;
            }))
            ->willThrowException(new JwtTokenExpiredException());

        $this->expectException(EmailConfigurationJwtTokenHasExpiredException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailErrorOnToken()
    {
        $token = 'token expired';
        /** @var MockObject|UserEmailConfirmationInputDto $input */
        $input = $this->getMockBuilder(UserEmailConfirmationInputDto::class)
            ->setConstructorArgs([$token])
            ->onlyMethods(['validate'])
            ->getMock();

        $input
            ->expects($this->once())
            ->method('validate')
            ->with($this->validator)
            ->willReturn([]);

        $this->emailConfirmationJwtTokenValidation
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (EmailConfirmationJwtTokenValidationDto $input) use ($token) {
                $this->assertEquals(ValueObjectFactory::createJwtToken($token), $input->token);

                return true;
            }))
            ->willThrowException(new JwtException());

        $this->expectException(EmailConfirmationJwtTokenNotValidException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailUserNotFound()
    {
        $token = 'token expired';
        /** @var MockObject|UserEmailConfirmationInputDto $input */
        $input = $this->getMockBuilder(UserEmailConfirmationInputDto::class)
            ->setConstructorArgs([$token])
            ->onlyMethods(['validate'])
            ->getMock();

        $input
            ->expects($this->once())
            ->method('validate')
            ->with($this->validator)
            ->willReturn([]);

        $this->emailConfirmationJwtTokenValidation
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (EmailConfirmationJwtTokenValidationDto $input) use ($token) {
                $this->assertEquals(ValueObjectFactory::createJwtToken($token), $input->token);

                return true;
            }))
            ->willThrowException(new DBNotFoundException());

        $this->expectException(EmailConfirmationJwtTokenNotValidException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailUserIsAlreadyActive22()
    {
        $token = 'token expired';
        /** @var MockObject|UserEmailConfirmationInputDto $input */
        $input = $this->getMockBuilder(UserEmailConfirmationInputDto::class)
            ->setConstructorArgs([$token])
            ->onlyMethods(['validate'])
            ->getMock();

        $input
            ->expects($this->once())
            ->method('validate')
            ->with($this->validator)
            ->willReturn([]);

        $this->emailConfirmationJwtTokenValidation
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (EmailConfirmationJwtTokenValidationDto $input) use ($token) {
                $this->assertEquals(ValueObjectFactory::createJwtToken($token), $input->token);

                return true;
            }))
            ->willThrowException(new InvalidArgumentException());

        $this->expectException(EmailConfirmationUserAlreadyActiveException::class);
        $this->object->__invoke($input);
    }
}
