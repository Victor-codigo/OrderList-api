<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Compiler;

use Common\Adapter\Compiler\KernelCustom;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

class KernelCustomTest extends TestCase
{
    private KernelCustom $object;
    private MockObject|Dotenv $dotEnv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dotEnv = $this->createMock(Dotenv::class);
        $this->object = new KernelCustom($this->dotEnv);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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
