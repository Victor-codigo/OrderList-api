<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Translator;

use Common\Adapter\Translator\TranslatorSymfonyAdapter;
use Common\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorSymfonyAdapterTest extends TestCase
{
    private TranslatorSymfonyAdapter $object;
    private MockObject|TranslatorInterface $translator;
    private MockObject|LocaleSwitcher $localeSwitcher;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->localeSwitcher = $this->createMock(LocaleSwitcher::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->object = new TranslatorSymfonyAdapter($this->translator, $this->localeSwitcher);
    }

    #[Test]
    public function translateIdentifier(): void
    {
        $id = 'identifier';
        $params = ['param' => 'hola'];
        $domain = 'domain';
        $locale = 'locale';
        $textTranslated = 'This has been translated';

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($id, $params, $domain, $locale)
            ->willReturn($textTranslated);

        $return = $this->object->translate($id, $params, $domain, $locale);

        $this->assertEquals($textTranslated, $return);
    }

    #[Test]
    public function translateThrowsInvalidArgumentException(): void
    {
        $id = 'identifier';
        $params = ['param' => 'hola'];
        $domain = 'domain';
        $locale = 'locale';

        $this->expectException(InvalidArgumentException::class);
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($id, $params, $domain, $locale)
            ->willThrowException(new \InvalidArgumentException());

        $this->object->translate($id, $params, $domain, $locale);
    }
}
