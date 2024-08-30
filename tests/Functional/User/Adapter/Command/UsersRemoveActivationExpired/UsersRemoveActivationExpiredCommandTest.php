<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Command\UsersRemoveActivationExpired;

use PHPUnit\Framework\Attributes\Test;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class UsersRemoveActivationExpiredCommandTest extends KernelTestCase
{
    use ReloadDatabaseTrait;

    private const string COMMAND = 'app:user:remove-not-active-expired';

    private Command $command;
    private Application $application;
    private CommandTester $commandTester;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$kernel = self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->command = $this->application->find(self::COMMAND);
        $this->commandTester = new CommandTester($this->command);
    }

    #[Test]
    public function itShouldRemoveUsersNotActiveAndTimeExpired(): void
    {
        $this->commandTester->execute([]);
        $this->commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('No users to remove', $this->commandTester->getDisplay());
    }
}
