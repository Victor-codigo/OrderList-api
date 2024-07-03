<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\HtmlTemplate;

use Override;
use Common\Adapter\HtmlTemplate\TwigTemplate;
use Common\Domain\HtmlTemplate\Exception\TemplateCantBeFoundException;
use Common\Domain\HtmlTemplate\Exception\TemplateRenderingException;
use Common\Domain\HtmlTemplate\Exception\TemplateSyntaxErrorException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Adapter\HtmlTemplate\Fixtures\TemplateParams;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TwigTemplateTest extends TestCase
{
    private TwigTemplate $object;
    private MockObject|Environment $twig;
    private string $templatePath = '';

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->twig = $this->createMock(Environment::class);
        $this->object = new TwigTemplate($this->twig);
    }

    /** @test */
    public function renderTemplateNoParamsToPass(): void
    {
        $renderReturn = 'template code';
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with($this->templatePath, [])
            ->willReturn($renderReturn);

        $return = $this->object->render($this->templatePath, null);

        $this->assertEquals($renderReturn, $return);
    }

    /** @test */
    public function renderTemplateWithParamsToPass(): void
    {
        $renderReturn = 'template code';
        $templateParams = new TemplateParams(1, 2);
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with($this->templatePath, $templateParams->toArray())
            ->willReturn($renderReturn);

        $return = $this->object->render($this->templatePath, $templateParams);

        $this->assertEquals($renderReturn, $return);
    }

    /** @test */
    public function renderTemplateSyntaxException(): void
    {
        $renderReturn = 'template code';

        $this->expectException(TemplateSyntaxErrorException::class);
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with($this->templatePath, [])
            ->willThrowException(new SyntaxError(''));

        $return = $this->object->render($this->templatePath, null);

        $this->assertEquals($renderReturn, $return);
    }

    /** @test */
    public function renderTemplateLoadException(): void
    {
        $renderReturn = 'template code';

        $this->expectException(TemplateCantBeFoundException::class);
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with($this->templatePath, [])
            ->willThrowException(new LoaderError(''));

        $return = $this->object->render($this->templatePath, null);

        $this->assertEquals($renderReturn, $return);
    }

    /** @test */
    public function renderTemplateRunTimeException(): void
    {
        $renderReturn = 'template code';

        $this->expectException(TemplateRenderingException::class);
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with($this->templatePath, [])
            ->willThrowException(new RuntimeError(''));

        $return = $this->object->render($this->templatePath, null);

        $this->assertEquals($renderReturn, $return);
    }
}
