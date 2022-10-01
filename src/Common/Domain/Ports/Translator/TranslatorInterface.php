<?php

namespace Common\Domain\Ports\Translator;

interface TranslatorInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function translate(string $id, array $params = [], string|null $domain = null, string|null $locale = null): string;

    public function setLocale(string $locale): void;

    public function getLocale(): string;

    public function resetLocale(): void;

    public function runWithLocale(string $locale, callable $callback): void;
}
