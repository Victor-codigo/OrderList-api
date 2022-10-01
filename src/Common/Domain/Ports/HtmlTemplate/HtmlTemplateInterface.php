<?php

namespace Common\Domain\Ports\HtmlTemplate;

interface HtmlTemplateInterface
{
    /**
     * @param array $params key - name
     *                      value - value
     *
     * @throws TemplateSyntaxErrorException
     * @throws TemplateCantBeFoundException
     * @throws TemplateRenderingException
     */
    public function render(string $templatePath, TemplateDtoInterface $params = null): string;
}
