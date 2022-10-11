<?php

namespace Common\Domain\HtmlTemplate\Exception;

use Common\Domain\Exception\DomainInternalErrorException;

class TemplateCantBeFoundException extends DomainInternalErrorException implements HtmlTemplateExceptionInterface
{
}
