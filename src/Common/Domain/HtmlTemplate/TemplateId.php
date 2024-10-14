<?php

declare(strict_types=1);

namespace Common\Domain\HtmlTemplate;

class TemplateId
{
    public readonly string $id;
    /**
     * @var array<string, string|int|float>
     */
    public readonly array $params;

    /**
     * @param array<string, string|int|float> $params
     */
    private function __construct(string $id, array $params = [])
    {
        $this->id = $id;
        $this->params = $params;
    }

    /**
     * @param array<string, string|int|float> $params
     */
    public static function create(string $id, array $params = []): self
    {
        return new self($id, $params);
    }
}
