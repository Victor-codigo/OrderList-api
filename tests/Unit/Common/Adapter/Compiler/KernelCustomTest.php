<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Compiler;

use Common\Adapter\Compiler\KernelCustom;
use PHPUnit\Framework\TestCase;

class KernelCustomTest extends TestCase
{
    /** @test */
    public function itShouldReturnDevEnvironmentParameterIsDev()
    {
        $environment = 'dev';
        $return = KernelCustom::changeEnviromentByRequestQuery($environment);

        $this->assertSame($environment, $return);
    }

    /** @test */
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotEnvParameter()
    {
        $environment = 'dev';
        $return = KernelCustom::changeEnviromentByRequestQuery($environment);

        $this->assertSame($environment, $return);
    }

    /** @test */
    public function itShouldReturnDevEnvironmentParameterDevRequestQueryNotValid()
    {
        $environment = 'dev';
        $_REQUEST['env'] = 'not valid environment';
        $return = KernelCustom::changeEnviromentByRequestQuery($environment);

        $this->assertSame($environment, $return);
    }

    /** @test */
    public function itShouldReturnTestEnvironmentParameterDevRequestQueryTest()
    {
        $environment = 'dev';
        $_REQUEST['env'] = 'test';
        $return = KernelCustom::changeEnviromentByRequestQuery($environment);

        $this->assertSame('test', $return);
    }
}
