<?php

declare(strict_types=1);

namespace Common\Domain\Mailer;

use Common\Domain\Ports\HtmlTemplate\TemplateDtoInterface;

class EmailDto
{
    public readonly string $subject;
    public readonly string $from;
    public readonly string $to;
    public readonly TemplateDtoInterface $dto;

    public function __construct(string $subject, string $from, string $to, TemplateDtoInterface $dto)
    {
        $this->subject = $subject;
        $this->from = $from;
        $this->to = $to;
        $this->dto = $dto;
    }
}
