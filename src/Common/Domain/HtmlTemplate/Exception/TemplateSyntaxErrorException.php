<?php

declare(strict_types=1);

namespace Common\Domain\HtmlTemplate\Exception;

use Common\Domain\Exception\DomainInternalErrorException;

class TemplateSyntaxErrorException extends DomainInternalErrorException implements HtmlTemplateExceptionInterface
{
}
