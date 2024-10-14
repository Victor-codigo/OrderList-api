<?php

declare(strict_types=1);

namespace Common\Domain\Ports\HtmlTemplate;

interface TemplateDtoInterface
{
    /**
     * @return mixed[]
     */
    public function toArray(): array;

    public function getPath(): string;
}
