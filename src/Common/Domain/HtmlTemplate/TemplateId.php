<?php

declare(strict_types=1);

namespace Common\Domain\HtmlTemplate;

class TemplateId
{
    public readonly string $id;
    public readonly array $params;

    private function __construct(string $id, array $params = [])
    {
        $this->id = $id;
        $this->params = $params;
    }

    public static function create(string $id, array $params = []): self
    {
        return new self($id, $params);
    }
}
