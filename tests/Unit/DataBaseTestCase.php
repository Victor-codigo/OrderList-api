<?php

declare(strict_types=1);

namespace Test\Unit;

use Common\Domain\Exception\LogicException;
use Common\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DataBaseTestCase extends KernelTestCase
{
    protected ?EntityManagerInterface $entityManager;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = static::bootKernel();

        if ('test' !== $kernel->getEnvironment()) {
            throw new LogicException('Only executable in test environment');
        }

        // @phpstan-ignore assign.propertyType
        $this->entityManager = $kernel
            ->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    #[\Override]
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function mockObjectManager(object $repository, MockObject&ObjectManager $objectManagerMock): void
    {
        $userRepositoryReflection = new \ReflectionClass($repository);
        $objectManagerProperty = $userRepositoryReflection->getProperty('objectManager');
        $objectManagerProperty->setValue($repository, $objectManagerMock);
    }
}
