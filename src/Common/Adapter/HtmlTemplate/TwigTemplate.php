<?php

namespace Common\Adapter\HtmlTemplate;

use Common\Domain\Exception\TemplateCantBeFoundException;
use Common\Domain\Exception\TemplateRenderingException;
use Common\Domain\Exception\TemplateSyntaxErrorException;
use Common\Domain\Ports\HtmlTemplate\HtmlTemplateInterface;
use Common\Domain\Ports\HtmlTemplate\TemplateDtoInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TwigTemplate implements HtmlTemplateInterface
{
    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param array $params key - name
     *                      value - value
     *
     * @throws TemplateSyntaxErrorException
     * @throws TemplateCantBeFoundException
     * @throws TemplateRenderingException
     */
    public function render(string $templatePath, TemplateDtoInterface $data = null): string
    {
        try {
            $templateData = [];

            if ($data instanceof TemplateDtoInterface) {
                $templateData = $data->toArray();
            }

            return $this->twig->render($templatePath, $templateData);
        } catch (SyntaxError $e) {
            throw TemplateSyntaxErrorException::create($e->getMessage(), $e->getCode());
        } catch (LoaderError $e) {
            throw TemplateCantBeFoundException::create($e->getMessage(), $e->getCode());
        } catch (RuntimeError $e) {
            throw TemplateRenderingException::create($e->getMessage(), $e->getCode());
        }
    }
}
