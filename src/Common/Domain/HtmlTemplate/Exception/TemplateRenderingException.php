<?php

namespace Common\Domain\HtmlTemplate\Exception;

use Common\Domain\Exception\DomainInternalErrorException;

class TemplateRenderingException extends DomainInternalErrorException implements HtmlTemplateExceptionInterface
{
}
