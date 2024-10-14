<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Command\HexFileStructure;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class HexFileStructureCommandTest extends KernelTestCase
{
    public const string OUTPUT_PATH = 'tests/Unit/Common/Adapter/Command/HexFileStructure/Fixtures/Output/FileStructure/';
    public const string OUTPUT_PATH_ALTERNATIVE = 'tests/Unit/Common/Adapter/Command/HexFileStructure/Fixtures/Output/FileStructure/Alternative/';
    public const string TEMPLATE_EXPECTED_PATH = 'tests/Unit/Common/Adapter/Command/HexFileStructure/Fixtures/Expected/';

    private const string COMMAND = 'app:file:structure';
    private const array COMMAND_PARAMS = [
        'module' => 'Module',
        'endpointName' => 'Endpoint',
        'layer' => 'all',
        'outputPath' => self::OUTPUT_PATH,
    ];

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

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteDirectory(self::OUTPUT_PATH.self::COMMAND_PARAMS['module']);
        $this->deleteDirectory(self::OUTPUT_PATH_ALTERNATIVE);
    }

    private function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ('.' == $item || '..' == $item) {
                continue;
            }

            if (!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * @param array<string, string> $params
     */
    public function assertDomainOutputIsCreated(array $params, string $outputPath): void
    {
        $outputServiceFile = $outputPath."{$params['module']}/Domain/Service/{$params['endpointName']}/{$params['endpointName']}Service.php";
        $this->assertFileExists($outputPath."{$params['module']}/Domain/Service/{$params['endpointName']}/{$params['endpointName']}Service.php");
        $this->assertFileEquals(
            self::TEMPLATE_EXPECTED_PATH.'Domain/Service/ExpectedService.php',
            $outputServiceFile
        );

        $outputServiceDtoFile = $outputPath."{$params['module']}/Domain/Service/{$params['endpointName']}/Dto/{$params['endpointName']}Dto.php";
        $this->assertFileExists($outputPath."{$params['module']}/Domain/Service/{$params['endpointName']}/Dto/{$params['endpointName']}Dto.php");
        $this->assertFileEquals(
            self::TEMPLATE_EXPECTED_PATH.'Domain/Service/Dto/ExpectedServiceDto.php',
            $outputServiceDtoFile
        );

        $outputDisplay = $this->commandTester->getDisplay();
        $this->assertStringContainsString('CREATED '.$outputServiceFile, $outputDisplay);
        $this->assertStringContainsString('CREATED '.$outputServiceDtoFile, $outputDisplay);
    }

    /**
     * @param array<string, string> $params
     */
    public function assertApplicationOutputIsCreated(array $params, string $outputPath): void
    {
        $outputUseCaseFile = $outputPath."{$params['module']}/Application/{$params['endpointName']}/{$params['endpointName']}UseCase.php";
        $this->assertFileExists($outputPath."{$params['module']}/Application/{$params['endpointName']}/{$params['endpointName']}UseCase.php");
        $this->assertFileEquals(
            self::TEMPLATE_EXPECTED_PATH.'Application/ExpectedUseCase.php',
            $outputUseCaseFile
        );

        $outputUseCaseInputDtoFile = $outputPath."{$params['module']}/Application/{$params['endpointName']}/Dto/{$params['endpointName']}InputDto.php";
        $this->assertFileExists($outputPath."{$params['module']}/Application/{$params['endpointName']}/Dto/{$params['endpointName']}InputDto.php");
        $this->assertFileEquals(
            self::TEMPLATE_EXPECTED_PATH.'Application/Dto/ExpectedUseCaseInputDto.php',
            $outputUseCaseInputDtoFile
        );

        $outputUseCaseOutputDtoFile = $outputPath."{$params['module']}/Application/{$params['endpointName']}/Dto/{$params['endpointName']}OutputDto.php";
        $this->assertFileExists($outputPath."{$params['module']}/Application/{$params['endpointName']}/Dto/{$params['endpointName']}OutputDto.php");
        $this->assertFileEquals(
            self::TEMPLATE_EXPECTED_PATH.'Application/Dto/ExpectedUseCaseOutputDto.php',
            $outputUseCaseOutputDtoFile
        );

        $outputDisplay = $this->commandTester->getDisplay();
        $this->assertStringContainsString('CREATED '.$outputUseCaseFile, $outputDisplay);
        $this->assertStringContainsString('CREATED '.$outputUseCaseInputDtoFile, $outputDisplay);
        $this->assertStringContainsString('CREATED '.$outputUseCaseOutputDtoFile, $outputDisplay);
    }

    /**
     * @param array<string, string> $params
     */
    public function assertAdapterOutputIsCreated(array $params, string $outputPath): void
    {
        $outputControllerFile = $outputPath."{$params['module']}/Adapter/Http/Controller/{$params['endpointName']}/{$params['endpointName']}Controller.php";
        $this->assertFileExists($outputPath."{$params['module']}/Adapter/Http/Controller/{$params['endpointName']}/{$params['endpointName']}Controller.php");
        $this->assertFileEquals(
            self::TEMPLATE_EXPECTED_PATH.'Adapter/Controller/ExpectedController.php',
            $outputControllerFile
        );

        $outputControllerDtoFile = $outputPath."{$params['module']}/Adapter/Http/Controller/{$params['endpointName']}/Dto/{$params['endpointName']}RequestDto.php";
        $this->assertFileExists($outputPath."{$params['module']}/Adapter/Http/Controller/{$params['endpointName']}/Dto/{$params['endpointName']}RequestDto.php");
        $this->assertFileEquals(
            self::TEMPLATE_EXPECTED_PATH.'Adapter/Controller/Dto/ExpectedRequestDto.php',
            $outputControllerDtoFile
        );

        $outputDisplay = $this->commandTester->getDisplay();
        $this->assertStringContainsString('CREATED '.$outputControllerFile, $outputDisplay);
        $this->assertStringContainsString('CREATED '.$outputControllerDtoFile, $outputDisplay);
    }

    #[Test]
    public function itShouldCreateTheDomainService(): void
    {
        $params = self::COMMAND_PARAMS;
        $params['layer'] = 'domain';
        $this->commandTester->execute($params);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertDomainOutputIsCreated($params, self::OUTPUT_PATH);
    }

    #[Test]
    public function itShouldCreateTheApplicationUseCase(): void
    {
        $params = self::COMMAND_PARAMS;
        $params['layer'] = 'APPLICATION';
        $this->commandTester->execute($params);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertApplicationOutputIsCreated($params, self::OUTPUT_PATH);
    }

    #[Test]
    public function itShouldCreateTheAdapterController(): void
    {
        $params = self::COMMAND_PARAMS;
        $params['layer'] = 'adapter';
        $this->commandTester->execute($params);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertAdapterOutputIsCreated($params, self::OUTPUT_PATH);
    }

    #[Test]
    public function itShouldCreateDomainApplicationAdapter(): void
    {
        $params = self::COMMAND_PARAMS;
        $this->commandTester->execute($params);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertDomainOutputIsCreated($params, self::OUTPUT_PATH);
        $this->assertApplicationOutputIsCreated($params, self::OUTPUT_PATH);
        $this->assertAdapterOutputIsCreated($params, self::OUTPUT_PATH);
    }

    #[Test]
    public function itShouldCreateDomainInADifferentOutputPath(): void
    {
        $params = self::COMMAND_PARAMS;
        $params['outputPath'] = self::OUTPUT_PATH_ALTERNATIVE;
        $this->commandTester->execute($params);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertDomainOutputIsCreated($params, self::OUTPUT_PATH_ALTERNATIVE);
        $this->assertApplicationOutputIsCreated($params, self::OUTPUT_PATH_ALTERNATIVE);
        $this->assertAdapterOutputIsCreated($params, self::OUTPUT_PATH_ALTERNATIVE);
    }

    #[Test]
    public function itShouldFail(): void
    {
        $params = self::COMMAND_PARAMS;
        $params['outputPath'] = self::OUTPUT_PATH_ALTERNATIVE;
        $this->commandTester->execute($params);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertDomainOutputIsCreated($params, self::OUTPUT_PATH_ALTERNATIVE);
        $this->assertApplicationOutputIsCreated($params, self::OUTPUT_PATH_ALTERNATIVE);
        $this->assertAdapterOutputIsCreated($params, self::OUTPUT_PATH_ALTERNATIVE);
    }
}
