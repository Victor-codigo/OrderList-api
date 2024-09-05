<?php

declare(strict_types=1);

namespace Common\Adapter\Command\HexFilesStructure;

use Common\Adapter\Command\HexFilesStructure\Exception\TemplateErrorException;

class Template
{
    public function __construct(
        private string $templatePath,
        private string $templateDestinationPath,
    ) {
    }

    /**
     * @throws TemplateErrorException
     */
    private function compile(array $placeholders): string
    {
        try {
            if (!file_exists($this->templatePath)) {
                throw new \DomainException();
            }

            $fileContent = file_get_contents($this->templatePath);

            return str_ireplace(
                array_keys($placeholders),
                array_values($placeholders),
                $fileContent
            );
        } catch (\Throwable) {
            throw TemplateErrorException::fromMessage('The template could not be read');
        }
    }

    /**
     * @throws TemplateErrorException
     */
    public function createDestiny(array $placeholders): void
    {
        $templateCompiled = $this->compile($placeholders);

        try {
            $result = @file_put_contents($this->templateDestinationPath, $templateCompiled);

            if (false === $result) {
                throw new \DomainException();
            }
        } catch (\Throwable) {
            throw TemplateErrorException::fromMessage('The template could not be created');
        }
    }
}
