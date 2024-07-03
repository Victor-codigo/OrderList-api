<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserEmailChange;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Application\UserEmailChange\Dto\UserEmailChangeInputDto;
use User\Application\UserEmailChange\Exception\UserEmailChangeCreateNotificationException;
use User\Application\UserEmailChange\UserEmailChangeUseCase;
use User\Domain\Service\UserEmailChange\Dto\UserEmailChangeInputDto as UserEmailChangeInputServiceDto;
use User\Domain\Service\UserEmailChange\UserEmailChangeService;

class UserEmailChangeUseCaseTest extends TestCase
{
    private const string SYSTEM_KEY = 'systemKeyForDev';

    private UserEmailChangeUseCase $object;
    private MockObject|ValidationInterface $validator;
    private MockObject|UserEmailChangeService $userEmailChangeService;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(ValidationInterface::class);
        $this->userEmailChangeService = $this->createMock(UserEmailChangeService::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->object = new UserEmailChangeUseCase(
            $this->validator,
            $this->userEmailChangeService,
            $this->moduleCommunication,
            self::SYSTEM_KEY
        );
    }

    /** @test */
    public function itShouldChangeUserEmail(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $userEmail = 'user@email.com';
        $emailNew = 'user@emailNew.com';
        $password = 'password';
        $input = new UserEmailChangeInputDto($userId, $userEmail, $emailNew, $password);
        $inputService = new UserEmailChangeInputServiceDto(
            ValueObjectFactory::createEmail($userEmail),
            ValueObjectFactory::createEmail($emailNew),
            ValueObjectFactory::createPassword($password)
        );

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userEmailChangeService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputService);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $moduleCommunicationDto) use ($userId): bool {
                $this->assertEquals([$userId->getValue()], $moduleCommunicationDto->content['users_id']);
                $this->assertEquals(self::SYSTEM_KEY, $moduleCommunicationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::USER_EMAIL_CHANGED->value, $moduleCommunicationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::OK));

        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailChangeUserEmailNotificationError(): void
    {
        $userId = ValueObjectFactory::createIdentifier('user id');
        $userEmail = 'user@email.com';
        $emailNew = 'user@emailNew.com';
        $password = 'password';
        $input = new UserEmailChangeInputDto($userId, $userEmail, $emailNew, $password);
        $inputService = new UserEmailChangeInputServiceDto(
            ValueObjectFactory::createEmail($userEmail),
            ValueObjectFactory::createEmail($emailNew),
            ValueObjectFactory::createPassword($password)
        );

        $this->validator
            ->expects($this->once())
            ->method('validateValueObjectArray')
            ->willReturn([]);

        $this->userEmailChangeService
            ->expects($this->once())
            ->method('__invoke')
            ->with($inputService);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $moduleCommunicationDto) use ($userId): bool {
                $this->assertEquals([$userId->getValue()], $moduleCommunicationDto->content['users_id']);
                $this->assertEquals(self::SYSTEM_KEY, $moduleCommunicationDto->content['system_key']);
                $this->assertEquals(NOTIFICATION_TYPE::USER_EMAIL_CHANGED->value, $moduleCommunicationDto->content['type']);

                return true;
            }))
            ->willReturn(new ResponseDto(status: RESPONSE_STATUS::ERROR));

        $this->expectException(UserEmailChangeCreateNotificationException::class);
        $this->object->__invoke($input);
    }
}
