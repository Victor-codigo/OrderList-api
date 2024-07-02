<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Compiler;

use Common\Adapter\Compiler\KernelCustom;
use PHPUnit\Framework\TestCase;

class KernelCustomTest extends TestCase
{
    /** @test */
    public function itShouldReturnDevEnvironmentParameterIsDev(): void
    {
        $environment = 'dev';
        $return = KernelCustom::changeEnvironmentByRequestQuery($environment);

        $this->assertSame($environment, $return);
        $this->assertEquals($_ENV['APP_ENV'], 'test');
    }

    /** @test */
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotEnvParameter(): void
    {
        $environment = 'dev';
        $return = KernelCustom::changeEnvironmentByRequestQuery($environment);

        $this->assertSame($environment, $return);
        $this->assertEquals($_ENV['APP_ENV'], 'test');
    }

    /** @test */
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotValid(): void
    {
        $environment = 'dev';
        $_REQUEST['env'] = 'not valid environment';
        $return = KernelCustom::changeEnvironmentByRequestQuery($environment);

        $this->assertSame($environment, $return);
        $this->assertEquals($_ENV['APP_ENV'], $environment);
    }

    /** @test */
    public function itShouldReturnTestEnvironmentParameterDevRequestQueryTest(): void
    {
        $environment = 'dev';
        $_REQUEST['env'] = 'test';
        $return = KernelCustom::changeEnvironmentByRequestQuery($environment);

        $this->assertSame('test', $return);
        $this->assertEquals($_ENV['APP_ENV'], $_REQUEST['env']);
    }

    /** @test */
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotValidType(): void
    {
        $environment = 'dev';
        $_REQUEST['env'] = [];
        $return = KernelCustom::changeEnvironmentByRequestQuery($environment);

        $this->assertSame('dev', $return);
        $this->assertEquals($_ENV['APP_ENV'], 'test');
    }
}
