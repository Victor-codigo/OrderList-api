<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserPasswordRememberChange;

use Override;
use stdClass;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Application\UserPasswordRememberChange\Dto\UserPasswordRememberChangeInputDto;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeNotificationException;
use User\Application\UserPasswordRememberChange\UserPasswordRememberChangeUseCase;
use User\Domain\Service\UserPasswordChange\Dto\UserPasswordChangeDto;
use User\Domain\Service\UserPasswordChange\UserPasswordChangeService;

class UserPasswordRememberChangeUseCaseTest extends TestCase
{
    private const string SYSTEM_KEY = 'systemKeyForDev';

    private UserPasswordRememberChangeUseCase $object;
    private MockObject|UserPasswordChangeService $userPasswordChangeService;
    private MockObject|JwtHS256Interface $jwt;
    private MockObject|ValidationInterface $validator;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userPasswordChangeService = $this->createMock(UserPasswordChangeService::class);
        $this->jwt = $this->createMock(JwtHS256Interface::class);
        $this->validator = $this->createMock(ValidationInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->object = new UserPasswordRememberChangeUseCase(
            $this->userPasswordChangeService,
            $this->jwt,
            $this->validator,
            $this->moduleCommunication,
            self::SYSTEM_KEY
        );
    }

    /** @test */
    public function itShouldChangeThePassword(): void
    {
        $token = 'token';
        $userId = 'user id';
        $tokenDecoded = new stdClass();
        $tokenDecoded->username = $userId;
        $passwordNew = 'passwordNew';
        $passwordNewRepeat = 'passwordNewRepeat';
        $input = new UserPasswordRememberChangeInputDto($token, $passwordNew, $passwordNewRepeat);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->jwt
            ->expects($this->once())
            ->method('decode')
            ->with($token)
            ->willReturn($tokenDecoded);

        $this->jwt
            ->expects($this->once())
            ->method('hasExpired')
            ->with($tokenDecoded)
            ->willReturn(false);

        $this->userPasswordChangeService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserPasswordChangeDto $serviceInput) use ($userId, $passwordNew, $passwordNewRepeat) {
                $this->assertEquals($userId, $serviceInput->id);
                $this->assertEquals($passwordNew, $serviceInput->passwordNew->getValue());
                $this->assertEquals($passwordNewRepeat, $serviceInput->passwordNewRepeat->getValue());

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId) {
                $this->assertEquals([$userId], $notificationDto->content['users_id']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::OK));

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailChangeThePasswordNotificationError(): void
    {
        $token = 'token';
        $userId = 'user id';
        $tokenDecoded = new stdClass();
        $tokenDecoded->username = $userId;
        $passwordNew = 'passwordNew';
        $passwordNewRepeat = 'passwordNewRepeat';
        $input = new UserPasswordRememberChangeInputDto($token, $passwordNew, $passwordNewRepeat);

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->jwt
            ->expects($this->once())
            ->method('decode')
            ->with($token)
            ->willReturn($tokenDecoded);

        $this->jwt
            ->expects($this->once())
            ->method('hasExpired')
            ->with($tokenDecoded)
            ->willReturn(false);

        $this->userPasswordChangeService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserPasswordChangeDto $serviceInput) use ($userId, $passwordNew, $passwordNewRepeat) {
                $this->assertEquals($userId, $serviceInput->id);
                $this->assertEquals($passwordNew, $serviceInput->passwordNew->getValue());
                $this->assertEquals($passwordNewRepeat, $serviceInput->passwordNewRepeat->getValue());

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId) {
                $this->assertEquals([$userId], $notificationDto->content['users_id']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER->value, $notificationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::ERROR));

        $this->expectException(UserPasswordRememberChangeNotificationException::class);
        $this->object->__invoke($input);
    }
}
