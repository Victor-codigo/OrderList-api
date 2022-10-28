<?php

namespace User\Adapter\Templates\Email\EmailPasswordRemember;

use Common\Domain\HtmlTemplate\TemplateDtoBase;
use Common\Domain\HtmlTemplate\TemplateId;

final class EmailPasswordRememberDto extends TemplateDtoBase
{
    public const TEMPLATE_PATH = 'Email/EmailPasswordRemember/EmailPasswordRemember.html.twig';
    public const TRANSLATOR_DOMAIN = 'EmailPasswordRemember';

    public readonly string $appName;
    public readonly string $userName;
    public readonly TemplateId $title;
    public readonly TemplateId $welcome;
    public readonly string $urlPasswordRememberConfirm;
    public readonly TemplateId $buttonRestorationText;
    public readonly TemplateId $farewell;

    public function __invoke(
        string $appName,
        string $userName,
        TemplateId $title,
        TemplateId $welcome,
        string $urlPasswordRememberConfirm,
        TemplateId $buttonRestorationText,
        TemplateId $farewell
    ): self {
        $this->appName = $appName;
        $this->userName = $userName;
        $this->title = $title;
        $this->welcome = $welcome;
        $this->urlPasswordRememberConfirm = $urlPasswordRememberConfirm;
        $this->buttonRestorationText = $buttonRestorationText;
        $this->farewell = $farewell;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'appName' => $this->appName,
            'userName' => $this->userName,
            'title' => $this->translate($this->title),
            'welcome' => $this->translate($this->welcome),
            'urlPasswordRememberConfirm' => $this->urlPasswordRememberConfirm,
            'buttonRestorationText' => $this->translate($this->buttonRestorationText),
            'farewell' => $this->translate($this->farewell),
        ];
    }
}
