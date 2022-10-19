<?php

namespace User\Adapter\Templates\Email\EmailRegistrationConfirmation;

use Common\Domain\HtmlTemplate\TemplateDtoBase;
use Common\Domain\HtmlTemplate\TemplateId;

final class EmailRegistrationConfirmationDto extends TemplateDtoBase
{
    public const TEMPLATE_PATH = 'Email/EmailRegistrationConfirmation/EmailRegistrationConfirmation.html.twig';
    public const TRANSLATOR_DOMAIN = 'EmailRegistrationConfirmation';

    public readonly string $appName;
    public readonly TemplateId $title;
    public readonly TemplateId $welcome;
    public readonly string $urlRegistrationConfirmation;
    public readonly TemplateId $urlRegistrationConfirmationText;
    public readonly TemplateId $farewell;

    public function __invoke(
        string $appName,
        TemplateId $title,
        TemplateId $welcome,
        string $urlRegistrationConfirmation,
        TemplateId $urlRegistrationConfirmationText,
        TemplateId $farewell
    ): self {
        $this->appName = $appName;
        $this->title = $title;
        $this->welcome = $welcome;
        $this->urlRegistrationConfirmation = $urlRegistrationConfirmation;
        $this->urlRegistrationConfirmationText = $urlRegistrationConfirmationText;
        $this->farewell = $farewell;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'appName' => $this->appName,
            'title' => $this->translate($this->title),
            'welcome' => $this->translate($this->welcome),
            'urlRegistrationConfirmation' => $this->urlRegistrationConfirmation,
            'urlRegistrationConfirmationText' => $this->translate($this->urlRegistrationConfirmationText),
            'farewell' => $this->translate($this->farewell),
        ];
    }
}
