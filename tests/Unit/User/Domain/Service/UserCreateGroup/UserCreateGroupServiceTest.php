<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\UserCreateGroup;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Group\GROUP_TYPE;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Service\UserCreateGroup\Dto\UserCreateGroupDto;
use User\Domain\Service\UserCreateGroup\Exception\UserCreateGroupUserException;
use User\Domain\Service\UserCreateGroup\UserCreateGroupService;

class UserCreateGroupServiceTest extends TestCase
{
    private UserCreateGroupService $object;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->object = new UserCreateGroupService($this->moduleCommunication);
    }

    private function createModuleCommunicationConfigDto(): ModuleCommunicationConfigDto
    {
        return new ModuleCommunicationConfigDto(
            'group_create',
            'POST',
            ['api_version' => 1],
            [],
            [],
            'multipart/form-data',
            [
                'name' => 'userName',
                'description' => '',
                'type' => GROUP_TYPE::USER->value,
            ],
            [],
            true
        );
    }

    /** @test */
    public function itShouldFailCouldNotCreateTheGroupStatusError(): void
    {
        $name = ValueObjectFactory::createName('userName');
        $input = new UserCreateGroupDto($name);
        $requestConfiguration = $this->createModuleCommunicationConfigDto();

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $config) use ($requestConfiguration) {
                $this->assertEquals($requestConfiguration->route, $config->route);
                $this->assertEquals($requestConfiguration->method, $config->method);
                $this->assertEquals($requestConfiguration->attributes, $config->attributes);
                $this->assertEquals($requestConfiguration->query, $config->query);
                $this->assertEquals($requestConfiguration->files, $config->files);
                $this->assertEquals($requestConfiguration->contentType, $config->contentType);
                $this->assertStringStartsWith($requestConfiguration->content['name'], $config->content['name']);
                $this->assertEquals($requestConfiguration->content['description'], $config->content['description']);
                $this->assertEquals($requestConfiguration->content['type'], $config->content['type']);
                $this->assertEquals($requestConfiguration->cookies, $config->cookies);
                $this->assertEquals($requestConfiguration->authentication, $config->authentication);

                return true;
            }))
            ->willReturn(new ResponseDto([], [], '', RESPONSE_STATUS::ERROR));

        $this->expectException(UserCreateGroupUserException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailCouldNotCreateTheGroupErrors(): void
    {
        $name = ValueObjectFactory::createName('userName');
        $input = new UserCreateGroupDto($name);
        $requestConfiguration = $this->createModuleCommunicationConfigDto();

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $config) use ($requestConfiguration) {
                $this->assertEquals($requestConfiguration->route, $config->route);
                $this->assertEquals($requestConfiguration->method, $config->method);
                $this->assertEquals($requestConfiguration->attributes, $config->attributes);
                $this->assertEquals($requestConfiguration->query, $config->query);
                $this->assertEquals($requestConfiguration->files, $config->files);
                $this->assertEquals($requestConfiguration->contentType, $config->contentType);
                $this->assertStringStartsWith($requestConfiguration->content['name'], $config->content['name']);
                $this->assertEquals($requestConfiguration->content['description'], $config->content['description']);
                $this->assertEquals($requestConfiguration->content['type'], $config->content['type']);
                $this->assertEquals($requestConfiguration->cookies, $config->cookies);
                $this->assertEquals($requestConfiguration->authentication, $config->authentication);

                return true;
            }))
            ->willReturn(new ResponseDto([], [1, 2, 3], '', RESPONSE_STATUS::OK));

        $this->expectException(UserCreateGroupUserException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldCreateTheUserGroup(): void
    {
        $name = ValueObjectFactory::createName('userName');
        $input = new UserCreateGroupDto($name);
        $requestConfiguration = $this->createModuleCommunicationConfigDto();

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (ModuleCommunicationConfigDto $config) use ($requestConfiguration) {
                $this->assertEquals($requestConfiguration->route, $config->route);
                $this->assertEquals($requestConfiguration->method, $config->method);
                $this->assertEquals($requestConfiguration->attributes, $config->attributes);
                $this->assertEquals($requestConfiguration->query, $config->query);
                $this->assertEquals($requestConfiguration->files, $config->files);
                $this->assertEquals($requestConfiguration->contentType, $config->contentType);
                $this->assertStringStartsWith($requestConfiguration->content['name'], $config->content['name']);
                $this->assertEquals($requestConfiguration->content['description'], $config->content['description']);
                $this->assertEquals($requestConfiguration->content['type'], $config->content['type']);
                $this->assertEquals($requestConfiguration->cookies, $config->cookies);
                $this->assertEquals($requestConfiguration->authentication, $config->authentication);

                return true;
            }))
            ->willReturn(new ResponseDto([], [], '', RESPONSE_STATUS::OK));

        $this->object->__invoke($input);
    }
}
