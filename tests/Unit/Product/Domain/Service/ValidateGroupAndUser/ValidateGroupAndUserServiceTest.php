<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ValidateGroupAndUser;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateGroupAndUserServiceTest extends TestCase
{
    private ValidateGroupAndUserService $object;
    private MockObject&ModuleCommunicationInterface $moduleCommunication;
    private MockObject&ResponseDto $response;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->response = $this->createMock(ResponseDto::class);
        $this->object = new ValidateGroupAndUserService($this->moduleCommunication);
    }

    #[Test]
    public function itShouldValidateTheGroupAndTheUser(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');

        $this->response
            ->expects($this->once())
            ->method('getErrors')
            ->willReturn([]);

        $this->response
            ->expects($this->once())
            ->method('hasContent')
            ->willReturn(true);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $config) use ($groupId): bool {
                $this->assertEquals(1, $config->query['page']);
                $this->assertEquals(1, $config->query['page_items']);
                $this->assertEquals($groupId->getValue(), $config->attributes['group_id']);

                return true;
            }))
            ->willReturn($this->response);

        $this->object->__invoke($groupId);
    }

    #[Test]
    public function itShouldFailValidatingTheGroupAndTheUserGotErrors(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');

        $this->response
            ->expects($this->once())
            ->method('getErrors')
            ->willReturn(['error']);

        $this->response
            ->expects($this->never())
            ->method('hasContent');

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $config) use ($groupId): bool {
                $this->assertEquals(1, $config->query['page']);
                $this->assertEquals(1, $config->query['page_items']);
                $this->assertEquals($groupId->getValue(), $config->attributes['group_id']);

                return true;
            }))
            ->willReturn($this->response);

        $this->expectException(ValidateGroupAndUserException::class);
        $this->object->__invoke($groupId);
    }

    #[Test]
    public function itShouldFailValidatingTheGroupAndTheUserNoContent(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');

        $this->response
            ->expects($this->once())
            ->method('getErrors')
            ->willReturn([]);

        $this->response
            ->expects($this->once())
            ->method('hasContent')
            ->willReturn(false);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $config) use ($groupId): bool {
                $this->assertEquals(1, $config->query['page']);
                $this->assertEquals(1, $config->query['page_items']);
                $this->assertEquals($groupId->getValue(), $config->attributes['group_id']);

                return true;
            }))
            ->willReturn($this->response);

        $this->expectException(ValidateGroupAndUserException::class);
        $this->object->__invoke($groupId);
    }
}
