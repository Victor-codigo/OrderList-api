<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Translator;

interface TranslatorInterface
{
    /**
     * @param string[]|int[]|float[] $params
     *
     * @throws InvalidArgumentException
     */
    public function translate(string $id, array $params = [], ?string $domain = null, ?string $locale = null): string;

    public function setLocale(string $locale): void;

    public function getLocale(): string;

    public function resetLocale(): void;

    public function runWithLocale(string $locale, callable $callback): void;
}
