<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\HtmlTemplate;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Translator\TranslatorSymfonyAdapter;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\HtmlTemplate\TemplateDtoBase;
use Common\Domain\HtmlTemplate\TemplateId;
use Common\Domain\Ports\Translator\TranslatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

class TemplateDtoBaseTest extends TestCase
{
    private TemplateDtoBase $object;
    private MockObject|TranslatorInterface $translator;
    private MockObject|SymfonyTranslatorInterface $symfonyTranslator;
    private MockObject|LocaleSwitcher $symfonyLocaleSwitcher;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->symfonyTranslator = $this->getMockForAbstractClass(SymfonyTranslatorInterface::class);
        $this->symfonyLocaleSwitcher = $this->createMock(LocaleSwitcher::class);
        $this->translator = new TranslatorSymfonyAdapter($this->symfonyTranslator, $this->symfonyLocaleSwitcher);
        $this->object = $this->getMockBuilder(TemplateDtoBase::class)
            ->setConstructorArgs([$this->translator])
            ->getMock();
    }

    #[Test]
    public function translateThrowsInvalidArguemtexception(): void
    {
        $templateId = TemplateId::create('id', []);

        $this->expectException(InvalidArgumentException::class);

        $this->symfonyTranslator
            ->expects($this->once())
            ->method('trans')
            ->willThrowException(new InvalidArgumentException());

        $translateMethod = $this->setTranslatePublic();
        $translateMethod->invoke($this->object, $templateId);
    }

    #[Test]
    public function translateIsOk(): void
    {
        $templateId = TemplateId::create('id', []);
        $locale = 'es';
        $translatedText = 'Text translated';

        $this->symfonyTranslator
            ->expects($this->once())
            ->method('trans')
            ->willReturn($translatedText);

        $this->translator->setLocale($locale);
        $translateMethod = $this->setTranslatePublic();
        $return = $translateMethod->invoke($this->object, $templateId);

        $this->assertEquals($translatedText, $return);
    }

    private function setTranslatePublic(): \ReflectionMethod
    {
        $templateDtoBaseReflection = new \ReflectionClass($this->object);
        $translateMethod = $templateDtoBaseReflection->getMethod('translate');
        $translateMethod->setAccessible(true);

        return $translateMethod;
    }
}
