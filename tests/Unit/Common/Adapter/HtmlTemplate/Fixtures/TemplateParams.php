<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\HtmlTemplate\Fixtures;

use Common\Domain\Ports\HtmlTemplate\TemplateDtoInterface;

class TemplateParams implements TemplateDtoInterface
{
    public readonly int $param1;
    public readonly int $param2;

    public function __construct(int $param1, int $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }

    public function toArray(): array
    {
        return [
            'param1' => $this->param1,
            'param2' => $this->param2,
        ];
    }

    public function getPath(): string
    {
        return '';
    }
}
