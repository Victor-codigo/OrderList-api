<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserPasswordChange;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeInputDto;
use User\Application\UserPasswordChange\Exception\UserPasswordChangeNotificationException;
use User\Application\UserPasswordChange\UserPasswordChangeUseCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Service\UserPasswordChange\Dto\UserPasswordChangeDto;
use User\Domain\Service\UserPasswordChange\UserPasswordChangeService;

class UserPasswordChangeUseCaseTest extends TestCase
{
    private const SYSTEM_KEY = 'systemKeForDev';

    private UserPasswordChangeUseCase $object;
    private MockObject|UserPasswordChangeService $userPasswordChangeService;
    private MockObject|ValidationInterface $validator;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|User $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userPasswordChangeService = $this->createMock(UserPasswordChangeService::class);
        $this->validator = $this->createMock(ValidationInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->userSession = $this->createMock(User::class);
        $this->object = new UserPasswordChangeUseCase(
            $this->userPasswordChangeService,
            $this->validator,
            $this->moduleCommunication,
            self::SYSTEM_KEY
        );
    }

    /** @test */
    public function itShouldChangeThePassword(): void
    {
        $userId = 'user id';
        $passwordOld = 'password old';
        $passwordNew = 'password new';
        $passwordRepeatNew = 'passwordRepeatNew';
        $input = new UserPasswordChangeInputDto($this->userSession, $userId, $passwordOld, $passwordNew, $passwordRepeatNew);

        $this->userSession
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(Roles::create([USER_ROLES::ADMIN]));

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userPasswordChangeService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserPasswordChangeDto $serviceInput) use ($userId, $passwordOld, $passwordNew, $passwordRepeatNew) {
                $this->assertEquals($userId, $serviceInput->id->getValue());
                $this->assertEquals($passwordOld, $serviceInput->passwordOld->getValue());
                $this->assertEquals($passwordNew, $serviceInput->passwordNew->getValue());
                $this->assertEquals($passwordRepeatNew, $serviceInput->passwordNewRepeat->getValue());

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId) {
                $this->assertEquals([$userId], $notificationDto->content['users_id']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::OK));

        $return = $this->object->__invoke($input);

        $this->assertTrue($return->success);
    }

    /** @test */
    public function itShouldFailChangeThePasswordNotificationError(): void
    {
        $userId = 'user id';
        $passwordOld = 'password old';
        $passwordNew = 'password new';
        $passwordRepeatNew = 'passwordRepeatNew';
        $input = new UserPasswordChangeInputDto($this->userSession, $userId, $passwordOld, $passwordNew, $passwordRepeatNew);

        $this->userSession
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(Roles::create([USER_ROLES::ADMIN]));

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userPasswordChangeService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserPasswordChangeDto $serviceInput) use ($userId, $passwordOld, $passwordNew, $passwordRepeatNew) {
                $this->assertEquals($userId, $serviceInput->id->getValue());
                $this->assertEquals($passwordOld, $serviceInput->passwordOld->getValue());
                $this->assertEquals($passwordNew, $serviceInput->passwordNew->getValue());
                $this->assertEquals($passwordRepeatNew, $serviceInput->passwordNewRepeat->getValue());

                return true;
            }));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $notificationDto) use ($userId) {
                $this->assertEquals([$userId], $notificationDto->content['users_id']);
                $this->assertEquals(self::SYSTEM_KEY, $notificationDto->content['system_key']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::ERROR));

        $this->expectException(UserPasswordChangeNotificationException::class);
        $this->object->__invoke($input);
    }
}
