<?php

declare(strict_types=1);

namespace Common\Domain\HtmlTemplate;

use Common\Domain\Exception\InvalidArgumentException as InvalidArgumentDomainException;
use Common\Domain\Ports\HtmlTemplate\TemplateDtoInterface;
use Common\Domain\Ports\Translator\TranslatorInterface;

abstract class TemplateDtoBase implements TemplateDtoInterface
{
    public const TEMPLATE_PATH = '';
    public const TRANSLATOR_DOMAIN = '';

    protected readonly TranslatorInterface $translator;
    protected readonly string $path;

    abstract protected function setStaticData(): void;

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    public function setLocale(string $locale): static
    {
        $this->translator->setLocale($locale);

        return $this;
    }

    #[\Override]
    public function getPath(): string
    {
        return static::TEMPLATE_PATH;
    }

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->path = '';
    }

    /**
     * @throws InvalidArgumentDomainException
     */
    protected function translate(TemplateId $id): string
    {
        try {
            return $this->translator->translate(
                $id->id,
                $id->params,
                static::TRANSLATOR_DOMAIN,
                $this->translator->getLocale()
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidArgumentDomainException::fromMessage($e->getMessage());
        }
    }
}
