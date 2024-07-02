<?php

declare(strict_types=1);

namespace Common\Adapter\Translator;

use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Ports\Translator\TranslatorInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

class TranslatorSymfonyAdapter implements TranslatorInterface
{
    private SymfonyTranslatorInterface $translator;
    private LocaleSwitcher $localeSwitcher;

    public function __construct(SymfonyTranslatorInterface $translator, LocaleSwitcher $localeSwitcher)
    {
        $this->translator = $translator;
        $this->localeSwitcher = $localeSwitcher;
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function translate(string $id, array $params = [], string|null $domain = null, string|null $locale = null): string
    {
        try {
            return $this->translator->trans($id, $params, $domain, $locale);
        } catch (\InvalidArgumentException $e) {
            throw InvalidArgumentException::fromMessage($e->getMessage());
        }
    }

    #[\Override]
    public function setLocale(string $locale): void
    {
        $this->localeSwitcher->setLocale($locale);
    }

    #[\Override]
    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    #[\Override]
    public function resetLocale(): void
    {
        $this->localeSwitcher->reset();
    }

    #[\Override]
    public function runWithLocale(string $locale, callable $callback): void
    {
        $this->localeSwitcher->runWithLocale($locale, $callback);
    }
}
