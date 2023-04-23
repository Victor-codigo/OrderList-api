<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Command\UsersRemoveActivationExpired;

use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Test\KernelTestCaseBase;

class UsersRemoveActivationExpiredCommandTest extends KernelTestCaseBase
{
    use ReloadDatabaseTrait;

    private const COMMAND = 'app:user:remove-not-active-expired';

    private Command $command;
    private Application $application;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        self::$kernel = self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->command = $this->application->find(self::COMMAND);
        $this->commandTester = new CommandTester($this->command);
    }

    /** @test */
    public function itShouldRemoveUsersNotActiveAndTimeExpired(): void
    {
        $this->commandTester->execute([]);
        $this->commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('No users to remove', $this->commandTester->getDisplay());
    }
}
