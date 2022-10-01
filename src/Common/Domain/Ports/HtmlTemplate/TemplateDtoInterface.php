<?php

namespace Common\Domain\Ports\HtmlTemplate;

interface TemplateDtoInterface
{
    public function toArray(): array;

    public function getPath(): string;
}
