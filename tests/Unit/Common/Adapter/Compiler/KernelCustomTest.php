<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Compiler;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Compiler\KernelCustom;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

class KernelCustomTest extends TestCase
{
    private KernelCustom $object;
    private MockObject|Dotenv $dotEnv;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->dotEnv = $this->createMock(Dotenv::class);
        $this->object = new KernelCustom($this->dotEnv);
    }

    #[Test]
    public function itShouldReturnTestEnvironmentParameterIsTest(): void
    {
        $environment = 'test';
        $projectDir = 'path/to/project';

        $this->dotEnv
            ->expects($this->never())
            ->method('loadEnv');

        $return = $this->object->changeEnvironmentByRequestQuery($environment, $projectDir);

        $this->assertSame($environment, $return);
        $this->assertEquals($_ENV['APP_ENV'], 'test');
    }

    #[Test]
    public function itShouldReturnDevEnvironmentParameterIsDev(): void
    {
        $environment = 'dev';
        $projectDir = 'path/to/project';

        $this->dotEnv
            ->expects($this->never())
            ->method('loadEnv');

        $return = $this->object->changeEnvironmentByRequestQuery($environment, $projectDir);

        $this->assertSame($environment, $return);
        $this->assertEquals($_ENV['APP_ENV'], 'test');
    }

    #[Test]
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotEnvParameter(): void
    {
        $environment = 'dev';
        $projectDir = 'path/to/project';

        $this->dotEnv
            ->expects($this->never())
            ->method('loadEnv');

        $return = $this->object->changeEnvironmentByRequestQuery($environment, $projectDir);

        $this->assertSame($environment, $return);
        $this->assertEquals($_ENV['APP_ENV'], 'test');
    }

    #[Test]
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotValid(): void
    {
        $environment = 'dev';
        $projectDir = 'path/to/project';
        $_REQUEST['env'] = 'not valid environment';

        $this->dotEnv
            ->expects($this->once())
            ->method('loadEnv')
            ->with(
                "{$projectDir}/.env.{$_REQUEST['env']}",
                $environment,
                'dev',
                ['test'],
                false
            )
            ->willThrowException(new PathException(''));

        $this->expectException(PathException::class);
        $this->object->changeEnvironmentByRequestQuery($environment, $projectDir);
    }

    #[Test]
    public function itShouldReturnTestEnvironmentParameterDevRequestQueryTest(): void
    {
        $environment = 'dev';
        $projectDir = 'path/to/project';
        $_REQUEST['env'] = 'test';

        $this->dotEnv
            ->expects($this->once())
            ->method('loadEnv')
            ->with(
                "{$projectDir}/.env.{$_REQUEST['env']}",
                $environment,
                'dev',
                ['test'],
                false
            )
            ->willReturnCallback(function (): string {
                $_ENV['APP_ENV'] = $_REQUEST['env'];

                return $_REQUEST['env'];
            });

        $return = $this->object->changeEnvironmentByRequestQuery($environment, $projectDir);

        $this->assertSame('test', $return);
        $this->assertEquals($_ENV['APP_ENV'], $_REQUEST['env']);
    }

    #[Test]
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotValidType(): void
    {
        $environment = 'dev';
        $projectDir = 'path/to/project';
        $_REQUEST['env'] = [];

        $this->dotEnv
            ->expects($this->never())
            ->method('loadEnv');

        $return = $this->object->changeEnvironmentByRequestQuery($environment, $projectDir);

        $this->assertSame('dev', $return);
        $this->assertEquals($_ENV['APP_ENV'], 'test');
    }
}
