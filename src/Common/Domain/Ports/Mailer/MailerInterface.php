<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Mailer;

use Common\Domain\Ports\HtmlTemplate\TemplateDtoInterface;

interface MailerInterface
{
    /**
     * @throws MailerSentException
     */
    public function send(): void;

    public function from(string $from): self;

    public function to(string $to): self;

    public function subject(string $subject): self;

    public function text(string $text): self;

    public function html(string $html): self;

    public function template(TemplateDtoInterface $data): self;

    public function cc(string ...$text): self;

    public function attachFile(string $filePath, string $fileName, string $mimeType): self;
}
