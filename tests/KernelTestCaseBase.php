<?php

declare(strict_types=1);

namespace Test;

use Common\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class KernelTestCaseBase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
