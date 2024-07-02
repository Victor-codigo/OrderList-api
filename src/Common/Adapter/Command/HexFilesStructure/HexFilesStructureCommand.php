<?php

declare(strict_types=1);

namespace Common\Adapter\Command\HexFilesStructure;

use Common\Adapter\Command\HexFilesStructure\Exception\TemplateErrorException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    'app:file:structure',
    'Creates the files structure for a endpoint in an hexadecimal architecture',
    ['a:f:s'],
    false
)]
class HexFilesStructureCommand extends Command
{
    private const string TEMPLATES_PATH = __DIR__.'/Template';
    private const string OUTPUT_DEFAULT_PATH = 'src/';

    private const int PERMISSIONS = 0o755;

    private const string LAYER_ADAPTER_NAME = 'adapter';
    private const string LAYER_APPLICATION_NAME = 'application';
    private const string LAYER_DOMAIN_NAME = 'domain';
    private const string LAYER_ALL_NAME = 'all';

    private KernelInterface $kernel;

    private string $endpointName;
    private string $module;
    private string|null $layer;
    private string|null $outputPath;

    private readonly string $templatesPath;
    private array $filesCreated = [];

    public function __construct(
        KernelInterface $kernel
    ) {
        $this->kernel = $kernel;
        $this->templatesPath = realpath(self::TEMPLATES_PATH).'/';
        $this->outputPath = realpath(self::OUTPUT_DEFAULT_PATH).'/';
        $this->layer = self::LAYER_ALL_NAME;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'module',
            InputArgument::REQUIRED,
            'Name of the use case where the folder structure is going to be created'
        );

        $this->addArgument(
            'endpointName',
            InputArgument::REQUIRED,
            'Name to set to all classes and folders for the end point'
        );

        $this->addArgument(
            'layer',
            InputArgument::OPTIONAL,
            self::LAYER_ALL_NAME.'(default)|'.mb_strtolower(self::LAYER_ADAPTER_NAME).'|'.mb_strtolower(self::LAYER_APPLICATION_NAME).'|'.mb_strtolower(self::LAYER_DOMAIN_NAME).' Layer to create'
        );

        $this->addArgument(
            'outputPath',
            InputArgument::OPTIONAL,
            'Path where folders and files are created'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getInput($input);

        try {
            match ($this->layer) {
                self::LAYER_DOMAIN_NAME => $this->createDomain(),
                self::LAYER_APPLICATION_NAME => $this->createApplication(),
                self::LAYER_ADAPTER_NAME => $this->createAdapter(),
                default => [
                    $this->createDomain(),
                    $this->createApplication(),
                    $this->createAdapter(),
                ]
            };

            $this->writeOutput($output);

            return Command::SUCCESS;
        } catch (Exception) {
            return Command::FAILURE;
        }
    }

    private function getInput(InputInterface $input): void
    {
        $this->module = $input->getArgument('module');
        $this->endpointName = $input->getArgument('endpointName');
        $layer = $input->getArgument('layer');
        $outputPath = $input->getArgument('outputPath');

        $this->layer = mb_strtolower((string) ($layer ?? $this->layer));
        $this->outputPath = $outputPath ?? $this->outputPath;
    }

    private function writeOutput(OutputInterface $output): void
    {
        $projectDir = $this->kernel->getProjectDir().'/';
        $commandOutput = array_map(
            fn (string $file) => 'CREATED '.str_replace($projectDir, '', $file),
            $this->filesCreated
        );

        $output->writeln($commandOutput);
    }

    /**
     * @throws TemplateErrorException
     */
    private function createAdapter(): void
    {
        $this->createDirIfNotExists("{$this->module}/Adapter/Http/Controller/{$this->endpointName}/Dto");

        $this->createTemplateOutput(
            'Adapter/Controller/Controller.template',
            "{$this->module}/Adapter/Http/Controller/{$this->endpointName}/{$this->endpointName}Controller.php", [
                PLACEHOLDER::NAMESPACE->value => "{$this->module}\\Adapter\\Http\Controller\\{$this->endpointName}",
                PLACEHOLDER::ENDPOINT->value => $this->endpointName,
                PLACEHOLDER::NAMESPACE_INNER_LAYER->value => "{$this->module}\\Application\\{$this->endpointName}",
            ]
        );

        $this->createTemplateOutput(
            'Adapter/Controller/Dto/RequestDto.template',
            "{$this->module}/Adapter/Http/Controller/{$this->endpointName}/Dto/{$this->endpointName}RequestDto.php", [
                PLACEHOLDER::NAMESPACE->value => "{$this->module}\\Adapter\\Http\\Controller\\{$this->endpointName}\\Dto",
                PLACEHOLDER::ENDPOINT->value => $this->endpointName,
            ]
        );
    }

    /**
     * @throws TemplateErrorException
     */
    private function createApplication(): void
    {
        $this->createDirIfNotExists("{$this->module}/Application/{$this->endpointName}/Dto");
        $this->createDirIfNotExists("{$this->module}/Application/{$this->endpointName}/Exception");

        $this->createTemplateOutput(
            'Application/UseCase.template',
            "{$this->module}/Application/{$this->endpointName}/{$this->endpointName}UseCase.php", [
                PLACEHOLDER::NAMESPACE->value => "{$this->module}\\Application\\{$this->endpointName}",
                PLACEHOLDER::ENDPOINT->value => $this->endpointName,
                PLACEHOLDER::NAMESPACE_INNER_LAYER->value => "{$this->module}\\Domain\\Service\\{$this->endpointName}",
            ]
        );

        $this->createTemplateOutput(
            'Application/Dto/UseCaseInputDto.template',
            "{$this->module}/Application/{$this->endpointName}/Dto/{$this->endpointName}InputDto.php", [
                PLACEHOLDER::NAMESPACE->value => "{$this->module}\\Application\\{$this->endpointName}\\Dto",
                PLACEHOLDER::ENDPOINT->value => $this->endpointName,
            ]
        );

        $this->createTemplateOutput(
            'Application/Dto/UseCaseOutputDto.template',
            "{$this->module}/Application/{$this->endpointName}/Dto/{$this->endpointName}OutputDto.php", [
                PLACEHOLDER::NAMESPACE->value => "{$this->module}\\Application\\{$this->endpointName}\\Dto",
                PLACEHOLDER::ENDPOINT->value => $this->endpointName,
            ]
        );
    }

    /**
     * @throws TemplateErrorException
     */
    private function createDomain(): void
    {
        $this->createDirIfNotExists("{$this->module}/Domain/Service/{$this->endpointName}/Dto");

        $this->createTemplateOutput(
            'Domain/Service/Service.template',
            "{$this->module}/Domain/Service/{$this->endpointName}/{$this->endpointName}Service.php", [
                PLACEHOLDER::NAMESPACE->value => "{$this->module}\\Domain\\Service\\{$this->endpointName}",
                PLACEHOLDER::ENDPOINT->value => $this->endpointName,
            ]
        );

        $this->createTemplateOutput(
            'Domain/Service/Dto/ServiceDto.template',
            "{$this->module}/Domain/Service/{$this->endpointName}/Dto/{$this->endpointName}Dto.php", [
                PLACEHOLDER::NAMESPACE->value => "{$this->module}\\Domain\\Service\\{$this->endpointName}\\Dto",
                PLACEHOLDER::ENDPOINT->value => $this->endpointName,
            ]
        );
    }

    private function createDirIfNotExists(string $path): void
    {
        if (!file_exists($this->outputPath.$path)) {
            mkdir($this->outputPath.$path, self::PERMISSIONS, true);
        }
    }

    /**
     * @param string $templatePath            Path to templates relative to template's path
     * @param string $templateDestinationPath path to oturput relative to output's path
     * @param array  $placeholders            template's placeholders
     *
     * @throws TemplateErrorException
     */
    private function createTemplateOutput(string $templatePath, string $templateDestinationPath, array $placeholders): void
    {
        $serviceTemplate = new Template(
            $this->templatesPath.$templatePath,
            $this->outputPath.$templateDestinationPath
        );
        $serviceTemplate->createDestiny($placeholders);

        $this->filesCreated[] = $this->outputPath.$templateDestinationPath;
    }
}
