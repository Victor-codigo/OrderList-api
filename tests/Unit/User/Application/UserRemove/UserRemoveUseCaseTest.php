<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\UserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Service\Exception\DomainErrorException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use User\Application\UserRemove\Dto\UserRemoveInputDto;
use User\Application\UserRemove\Dto\UserRemoveOutputDto;
use User\Application\UserRemove\Exception\UserRemoveUserNotFoundException;
use User\Application\UserRemove\UserRemoveUseCase;
use User\Domain\Model\User;
use User\Domain\Service\UserRemove\Dto\UserRemoveDto;
use User\Domain\Service\UserRemove\UserRemoveService;

class UserRemoveUseCaseTest extends TestCase
{
    private const string USER_ID = '0dc1d61f-10e0-40f4-8296-d0aca1c13991';
    private const string SYSTEM_KEY = 'SystemDevKey';

    private UserRemoveUseCase $object;
    private MockObject&UserRemoveService $userRemoveService;
    private MockObject&ModuleCommunicationInterface $moduleCommunication;
    private MockObject&User $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRemoveService = $this->createMock(UserRemoveService::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->userSession = $this->createMock(User::class);
        $this->object = new UserRemoveUseCase(
            $this->userRemoveService,
            $this->moduleCommunication,
            self::SYSTEM_KEY
        );
    }

    /**
     * @param array<string, string> $errors
     */
    private function getRemoveGroupsResponse(RESPONSE_STATUS $status, bool $hasErrors, array $errors): ResponseDto
    {
        if ($hasErrors && empty($errors)) {
            $errors = [
                'group_id' => 'not_blank',
            ];
        }

        return new ResponseDto(
            [
                'groups_id_removed' => [
                    '5753d1d3-780a-40c0-928b-839dc96e4950',
                    'd0142e5a-c5d4-45b7-ad2d-b99055db40c4',
                    'c87cd6a4-21e0-4931-a912-41634d2545aa',
                ],
                'groups_id_user_removed' => [
                    '6ef25c67-7b33-48c4-a3aa-c1e57a040838',
                    'a60eed7d-a290-4152-b48b-6fc954b47fbb',
                    '527b9424-0d07-4d82-a5cf-3f1548eb91a5',
                ],
                'groups_id_user_set_as_admin' => [
                    [
                        'group_id' => '00b3ea06-13e3-420b-92e6-b150407936cc',
                        'user_id' => '9a2d03b8-df1e-49f2-b5c2-de456403c65f',
                    ],
                    [
                        'group_id' => 'b5c3dda8-2d6a-4918-b009-3ad6a2376781',
                        'user_id' => '23e0a099-a531-4cc8-9a5c-33b4f5c1396a',
                    ],
                    [
                        'group_id' => '1961e263-7bf7-483f-918e-05fcab878de6',
                        'user_id' => '50aaf30c-fba1-47b0-88d0-c66736b0afce',
                    ],
                ],
            ],
            $hasErrors ? $errors : [],
            'Remove group response',
            $status,
        );
    }

    private function getRemoveNotificationsResponse(RESPONSE_STATUS $status, bool $hasErrors): ResponseDto
    {
        $errors = [
            'group_id' => 'not_null',
        ];

        return new ResponseDto(
            [
                'id' => [
                    '2484525b-298d-4a2d-ab2c-ec9e0292e747',
                    '4c4f2c62-49c9-4b7b-a23e-d964de17eba0',
                    '073e6b66-32bd-45da-8457-26d9db473de5',
                ],
            ],
            $hasErrors ? $errors : [],
            'Remove notifications response',
            $status,
        );
    }

    private function getRemoveProductsResponse(ResponseDto $removeGroupsResponse, RESPONSE_STATUS $status, bool $hasErrors): ResponseDto
    {
        $errors = [
            'group_id' => 'not_null',
        ];

        return new ResponseDto(
            [
                'id' => $removeGroupsResponse->data['groups_id_removed'],
            ],
            $hasErrors ? $errors : [],
            'Remove products response',
            $status,
        );
    }

    private function getRemoveShopsResponse(ResponseDto $removeGroupsResponse, RESPONSE_STATUS $status, bool $hasErrors): ResponseDto
    {
        $errors = [
            'group_id' => 'not_null',
        ];

        return new ResponseDto(
            [
                'id' => $removeGroupsResponse->data['groups_id_removed'],
            ],
            $hasErrors ? $errors : [],
            'Remove shops response',
            $status,
        );
    }

    private function getRemoveOrdersResponse(ResponseDto $removeGroupsResponse, RESPONSE_STATUS $status, bool $hasErrors): ResponseDto
    {
        $errors = [
            'group_id' => 'not_null',
        ];

        return new ResponseDto(
            [
                'orders_id_removed' => $removeGroupsResponse->data['groups_id_removed'],
                'orders_id_user_changed' => [
                    ...$removeGroupsResponse->data['groups_id_user_removed'],
                    ...$removeGroupsResponse->data['groups_id_user_set_as_admin'],
                ],
            ],
            $hasErrors ? $errors : [],
            'Remove shops response',
            $status,
        );
    }

    private function getRemoveListsOrdersResponse(ResponseDto $removeGroupsResponse, RESPONSE_STATUS $status, bool $hasErrors): ResponseDto
    {
        $errors = [
            'group_id' => 'not_null',
        ];

        return new ResponseDto(
            [
                'orders_id_removed' => $removeGroupsResponse->data['groups_id_removed'],
                'orders_id_user_changed' => [
                    ...$removeGroupsResponse->data['groups_id_user_removed'],
                    ...$removeGroupsResponse->data['groups_id_user_set_as_admin'],
                ],
            ],
            $hasErrors ? $errors : [],
            'Remove shops response',
            $status,
        );
    }

    private function assertModuleCommunicationConfigDtoIdOk(ModuleCommunicationConfigDto $configActual, ResponseDto $removeGroupsResponse, InvokedCount $moduleCommunicationMatcher): bool
    {
        $groupsIdRemoved = array_map(
            fn (string $groupId): Identifier => ValueObjectFactory::createIdentifier($groupId),
            $removeGroupsResponse->data['groups_id_removed']
        );
        $groupsIdUserRemoved = array_map(
            fn (string $groupId): Identifier => ValueObjectFactory::createIdentifier($groupId),
            $removeGroupsResponse->data['groups_id_user_removed']
        );

        match ($moduleCommunicationMatcher->numberOfInvocations()) {
            1 => $this->assertEquals(ModuleCommunicationFactory::groupRemoveAllUserGroups(self::SYSTEM_KEY), $configActual),
            2 => $this->assertEquals(ModuleCommunicationFactory::notificationsRemoveAllUserNotifications(self::SYSTEM_KEY), $configActual),
            3 => $this->assertEquals(ModuleCommunicationFactory::productRemoveGroupsProducts($groupsIdRemoved, self::SYSTEM_KEY), $configActual),
            4 => $this->assertEquals(ModuleCommunicationFactory::shopRemoveGroupsShops($groupsIdRemoved, self::SYSTEM_KEY), $configActual),
            5 => $this->assertEquals(ModuleCommunicationFactory::ordersRemoveAllUserOrdersOrChangeUserId(
                $groupsIdRemoved,
                $groupsIdUserRemoved,
                self::SYSTEM_KEY
            ),
                $configActual
            ),
            6 => $this->assertEquals(ModuleCommunicationFactory::listOrdersRemoveAllUserListOrdersOrChangeUserId(
                $groupsIdRemoved,
                $groupsIdUserRemoved,
                self::SYSTEM_KEY
            ),
                $configActual
            ),
            default => throw new \LogicException('Not supporting more than 6 invocations'),
        };

        return true;
    }

    #[Test]
    public function itShouldRemoveUserAndAllItsDependencies(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeOrdersResponse = $this->getRemoveOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeListsOrdersResponse = $this->getRemoveListsOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $returnExpected = new UserRemoveOutputDto($userId);

        $moduleCommunicationMatcher = $this->exactly(6);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
                $removeOrdersResponse,
                $removeListsOrdersResponse
            );

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->userRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserRemoveDto $userRemoveDto) use ($userId): bool {
                $this->assertEquals($userId, $userRemoveDto->userId);

                return true;
            }))
            ->willReturn($userId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($returnExpected, $return);
    }

    #[Test]
    public function itShouldRemoveUserGroupNoGroups(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::ERROR, true, ['group_not_found' => 'message']);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $returnExpected = new UserRemoveOutputDto($userId);

        $moduleCommunicationMatcher = $this->exactly(2);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnCallback(fn (): ResponseDto => match ($moduleCommunicationMatcher->numberOfInvocations()) {
                1 => $removeGroupsResponse ,
                2 => $removeNotificationsResponse,
                default => throw new \LogicException('Not Supporting more than 2 invocations'),
            });

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->userRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserRemoveDto $userRemoveDto) use ($userId): bool {
                $this->assertEquals($userId, $userRemoveDto->userId);

                return true;
            }))
            ->willReturn($userId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($returnExpected, $return);
    }

    #[Test]
    public function itShouldFailErrorRemovingGroupsStatusError(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::ERROR, true, []);

        $moduleCommunicationMatcher = $this->once();
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturn($removeGroupsResponse);

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailErrorRemovingGroupsHasErrors(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, true, []);

        $moduleCommunicationMatcher = $this->once();
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturn($removeGroupsResponse);

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemoveNotificationsStatusError(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::ERROR, false);

        $moduleCommunicationMatcher = $this->exactly(2);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(
                fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher))
            )
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemoveNotificationsHasErrors(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, true);

        $moduleCommunicationMatcher = $this->exactly(2);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(
                fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher))
            )
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemoveProductsStatusError(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::ERROR, false);

        $moduleCommunicationMatcher = $this->exactly(3);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemoveProductsHasErrors(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, true);

        $moduleCommunicationMatcher = $this->exactly(3);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemovingShopStatusError(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::ERROR, false);

        $moduleCommunicationMatcher = $this->exactly(4);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemovingShopHasErrors(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, true);

        $moduleCommunicationMatcher = $this->exactly(4);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemovingOrdersStatusError(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeOrdersResponse = $this->getRemoveOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::ERROR, false);

        $moduleCommunicationMatcher = $this->exactly(5);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
                $removeOrdersResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemovingOrdersHasErrors(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeOrdersResponse = $this->getRemoveOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, true);

        $moduleCommunicationMatcher = $this->exactly(5);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
                $removeOrdersResponse,
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemovingListOrdersStatusError(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeOrdersResponse = $this->getRemoveOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeListsOrdersResponse = $this->getRemoveListsOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::ERROR, false);

        $moduleCommunicationMatcher = $this->exactly(6);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
                $removeOrdersResponse,
                $removeListsOrdersResponse
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemovingListOrdersHasErrors(): void
    {
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeOrdersResponse = $this->getRemoveOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeListsOrdersResponse = $this->getRemoveListsOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, true);

        $moduleCommunicationMatcher = $this->exactly(6);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
                $removeOrdersResponse,
                $removeListsOrdersResponse
            );

        $this->userSession
            ->expects($this->never())
            ->method('getId');

        $this->userRemoveService
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailNoUserToRemoveFoundException(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeOrdersResponse = $this->getRemoveOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeListsOrdersResponse = $this->getRemoveListsOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);

        $moduleCommunicationMatcher = $this->exactly(6);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
                $removeOrdersResponse,
                $removeListsOrdersResponse
            );

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->userRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserRemoveDto $userRemoveDto) use ($userId): bool {
                $this->assertEquals($userId, $userRemoveDto->userId);

                return true;
            }))
            ->willThrowException(new DBNotFoundException());

        $this->expectException(UserRemoveUserNotFoundException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailNoUserToRemoveErrorException(): void
    {
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $input = new UserRemoveInputDto($this->userSession);
        $removeGroupsResponse = $this->getRemoveGroupsResponse(RESPONSE_STATUS::OK, false, []);
        $removeNotificationsResponse = $this->getRemoveNotificationsResponse(RESPONSE_STATUS::OK, false);
        $removeProductsResponse = $this->getRemoveProductsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeShopsResponse = $this->getRemoveShopsResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeOrdersResponse = $this->getRemoveOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);
        $removeListsOrdersResponse = $this->getRemoveListsOrdersResponse($removeGroupsResponse, RESPONSE_STATUS::OK, false);

        $moduleCommunicationMatcher = $this->exactly(6);
        $this->moduleCommunication
            ->expects($moduleCommunicationMatcher)
            ->method('__invoke')
            ->with($this->callback(fn (ModuleCommunicationConfigDto $config): bool => $this->assertModuleCommunicationConfigDtoIdOk($config, $removeGroupsResponse, $moduleCommunicationMatcher)))
            ->willReturnOnConsecutiveCalls(
                $removeGroupsResponse,
                $removeNotificationsResponse,
                $removeProductsResponse,
                $removeShopsResponse,
                $removeOrdersResponse,
                $removeListsOrdersResponse
            );

        $this->userSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->userRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (UserRemoveDto $userRemoveDto) use ($userId): bool {
                $this->assertEquals($userId, $userRemoveDto->userId);

                return true;
            }))
            ->willThrowException(new DomainInternalErrorException());

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }
}
