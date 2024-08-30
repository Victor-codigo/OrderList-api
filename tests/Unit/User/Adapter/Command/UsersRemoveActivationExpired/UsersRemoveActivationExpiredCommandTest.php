<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Command\UsersRemoveActivationExpired;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Adapter\Command\UsersRemoveActivationExpired\UsersRemoveActivationExpiredCommand;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;

class UsersRemoveActivationExpiredCommandTest extends TestCase
{
    private const int USER_NOT_ACTIVE_TIME_TO_EXPIRE = 100;

    private MockObject|UserRepositoryInterface $userRepository;
    private MockObject|User $userNotActive;
    private MockObject|PaginatorInterface $paginator;
    private MockObject|InputInterface $input;
    private MockObject|OutputInterface $output;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userNotActive = $this->createMock(User::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    }

    private function executeInvoke(Command $command, InputInterface $input, OutputInterface $output): int
    {
        $commandReflection = new \ReflectionClass($command);
        $method = $commandReflection->getMethod('execute');
        $method->setAccessible(true);

        return $method->invoke($command, $input, $output);
    }

    #[Test]
    public function itShouldRemoveUsersNotActiveAndTimeExpired(): void
    {
        $pagesNum = 3;
        $object = new UsersRemoveActivationExpiredCommand($this->userRepository, self::USER_NOT_ACTIVE_TIME_TO_EXPIRE);

        $this->paginator
            ->expects($this->once())
            ->method('getPagesTotal')
            ->willReturn($pagesNum);

        $this->paginator
            ->expects($this->exactly($pagesNum))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->userNotActive]));

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersTimeActivationExpiredOrFail')
            ->with(self::USER_NOT_ACTIVE_TIME_TO_EXPIRE)
            ->willReturn($this->paginator);

        $this->userRepository
            ->expects($this->exactly($pagesNum))
            ->method('remove')
            ->with([$this->userNotActive]);

        $return = $this->executeInvoke($object, $this->input, $this->output);

        $this->assertEquals(Command::SUCCESS, $return);
    }

    #[Test]
    public function itShouldRemoveUsersNotActiveAndTimeExpiredNoUserToRemove(): void
    {
        $object = new UsersRemoveActivationExpiredCommand($this->userRepository, self::USER_NOT_ACTIVE_TIME_TO_EXPIRE);

        $this->paginator
            ->expects($this->never())
            ->method('getPagesTotal');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersTimeActivationExpiredOrFail')
            ->with(self::USER_NOT_ACTIVE_TIME_TO_EXPIRE)
            ->willThrowException(new DBNotFoundException());

        $this->userRepository
            ->expects($this->never())
            ->method('remove');

        $return = $this->executeInvoke($object, $this->input, $this->output);

        $this->assertEquals(Command::SUCCESS, $return);
    }

    #[Test]
    public function itShouldFailRemoveUsersNotActiveAndTimeExpiredErrorRemoving(): void
    {
        $pagesNum = 3;
        $object = new UsersRemoveActivationExpiredCommand($this->userRepository, self::USER_NOT_ACTIVE_TIME_TO_EXPIRE);

        $this->paginator
            ->expects($this->once())
            ->method('getPagesTotal')
            ->willReturn($pagesNum);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->userNotActive]));

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersTimeActivationExpiredOrFail')
            ->with(self::USER_NOT_ACTIVE_TIME_TO_EXPIRE)
            ->willReturn($this->paginator);

        $this->userRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$this->userNotActive])
            ->willThrowException(new DBConnectionException());

        $return = $this->executeInvoke($object, $this->input, $this->output);

        $this->assertEquals(Command::FAILURE, $return);
    }
}
