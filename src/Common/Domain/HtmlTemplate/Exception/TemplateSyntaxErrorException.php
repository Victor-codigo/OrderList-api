<?php

namespace Common\Domain\HtmlTemplate\Exception;

use Common\Domain\Exception\DomainInternalErrorException;

class TemplateSyntaxErrorException extends DomainInternalErrorException implements HtmlTemplateExceptionInterface
{
}
