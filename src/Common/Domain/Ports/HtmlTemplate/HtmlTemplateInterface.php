<?php

declare(strict_types=1);

namespace Common\Domain\Ports\HtmlTemplate;

use Common\Domain\HtmlTemplate\Exception\TemplateCantBeFoundException;
use Common\Domain\HtmlTemplate\Exception\TemplateRenderingException;
use Common\Domain\HtmlTemplate\Exception\TemplateSyntaxErrorException;

interface HtmlTemplateInterface
{
    /**
     * @throws TemplateSyntaxErrorException
     * @throws TemplateCantBeFoundException
     * @throws TemplateRenderingException
     */
    public function render(string $templatePath, ?TemplateDtoInterface $params = null): string;
}
