<?php

declare(strict_types=1);

namespace Common\Adapter\HtmlTemplate;

use Common\Domain\HtmlTemplate\Exception\TemplateCantBeFoundException;
use Common\Domain\HtmlTemplate\Exception\TemplateRenderingException;
use Common\Domain\HtmlTemplate\Exception\TemplateSyntaxErrorException;
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
     * @throws TemplateSyntaxErrorException
     * @throws TemplateCantBeFoundException
     * @throws TemplateRenderingException
     */
    #[\Override]
    public function render(string $templatePath, ?TemplateDtoInterface $data = null): string
    {
        try {
            $templateData = [];

            if ($data instanceof TemplateDtoInterface) {
                $templateData = $data->toArray();
            }

            return $this->twig->render($templatePath, $templateData);
        } catch (SyntaxError $e) {
            throw TemplateSyntaxErrorException::fromMessage($e->getMessage(), $e->getCode());
        } catch (LoaderError $e) {
            throw TemplateCantBeFoundException::fromMessage($e->getMessage(), $e->getCode());
        } catch (RuntimeError $e) {
            throw TemplateRenderingException::fromMessage($e->getMessage(), $e->getCode());
        }
    }
}
