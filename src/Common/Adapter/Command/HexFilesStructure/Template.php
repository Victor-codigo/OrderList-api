<?php

declare(strict_types=1);

namespace Common\Adapter\Command\HexFilesStructure;

use Exception;
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
            $fileContent = file_get_contents($this->templatePath);

            return str_ireplace(
                array_keys($placeholders),
                array_values($placeholders),
                $fileContent
            );
        } catch (Exception) {
            throw TemplateErrorException::fromMessage('The template could not be readed');
        }
    }

    /**
     * @throws TemplateErrorException
     */
    public function createDestiny(array $placeholders): void
    {
        $templateCompiled = $this->compile($placeholders);

        try {
            file_put_contents($this->templateDestinationPath, $templateCompiled);
        } catch (Exception) {
            throw TemplateErrorException::fromMessage('The template could not be created');
        }
    }
}
