<?php

declare(strict_types=1);

namespace Test\Functional\Share\Adapter\Command\ShareRemoveExpiredRecoursers;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\ORM\EntityManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Share\Adapter\Command\ShareRemoveExpiredRecoursesCommand;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareRecoursesRemove\ShareRecoursesRemoveService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ShareRemoveExpiredRecoursesCommandTest extends KernelTestCase
{
    use ReloadDatabaseTrait;

    private const string COMMAND = 'app:share:remove';
    private const string SHARE_ID_EXIST = '72b37f9c-ff55-4581-a131-4270e73012a2';
    private const string SHARE_ID_EXIST_2 = '8aaa96f5-cc54-45cb-bf43-f9b8fe256696';
    private const string SHARE_ID_EXIST_3 = '5552b4a6-8326-462a-a42b-f60b33640aef';
    private const string SHARE_ID_EXIST_4 = 'b70761dd-d32a-4434-aadb-25e694f50a22';

    private Application $application;
    private Command $command;
    private CommandTester $commandTester;
    private ShareRepositoryInterface $shareRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$kernel = self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->command = $this->application->find(self::COMMAND);
        $this->commandTester = new CommandTester($this->command);

        /** @var EntityManager $entityManager */
        $entityManager = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->shareRepository = $entityManager->getRepository(Share::class);
    }

    private function removeSharedRecoursesExpired(): void
    {
        $sharedResources = $this->shareRepository->findSharedRecursesByIdOrFail([
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_2),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_3),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_4),
        ]);
        $this->shareRepository->remove(iterator_to_array($sharedResources));
    }

    #[Test]
    public function itShouldRemoveSharedExpiredRecourses(): void
    {
        $this->commandTester->execute([]);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            'Shared recourses removed successfully',
            $this->commandTester->getDisplay()
        );
    }

    #[Test]
    public function itShouldRemoveSharedExpiredRecoursesNotFound(): void
    {
        $this->removeSharedRecoursesExpired();
        $this->commandTester->execute([]);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            'No shared recourses to remove',
            $this->commandTester->getDisplay()
        );
    }

    #[Test]
    public function itShouldFailRemovingSharedExpiredRecourses(): void
    {
        /** @var MockObject&ShareRecoursesRemoveService $shareRecurseRemoveService */
        $shareRecurseRemoveService = $this->createMock(ShareRecoursesRemoveService::class);

        $shareRecurseRemoveService
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new DBConnectionException());

        $command = new ShareRemoveExpiredRecoursesCommand(
            $this->shareRepository,
            $shareRecurseRemoveService
        );

        $commandTester = new CommandTester($command);
        $return = $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $return);
    }
}
