<?php

declare(strict_types=1);

namespace User\Adapter\Templates\Email\EmailRegistrationConfirmation;

use Common\Domain\HtmlTemplate\TemplateDtoBase;
use Common\Domain\HtmlTemplate\TemplateId;

final class EmailRegistrationConfirmationDto extends TemplateDtoBase
{
    public const TEMPLATE_PATH = 'Email/EmailRegistrationConfirmation/EmailRegistrationConfirmation.html.twig';
    public const TRANSLATOR_DOMAIN = 'EmailRegistrationConfirmation';

    private string $appName;
    private string $urlRegistrationConfirmation;
    private string $emailUserRegistrationConfirmationExpire;

    private TemplateId $title;
    private TemplateId $welcome;
    private TemplateId $urlRegistrationConfirmationText;
    private TemplateId $farewell;

    public function setData(string $appName, string $urlRegistrationConfirmation, int $emailUserRegistrationConfirmationExpire): static
    {
        $this->appName = $appName;
        $this->urlRegistrationConfirmation = $urlRegistrationConfirmation;
        $this->emailUserRegistrationConfirmationExpire = $emailUserRegistrationConfirmationExpire;

        $this->setStaticData();

        return $this;
    }

    protected function setStaticData(): void
    {
        $this->title = TemplateId::create('title');
        $this->welcome = TemplateId::create('welcome', ['appName' => $this->appName]);
        $this->urlRegistrationConfirmationText = TemplateId::create('urlRegistrationConfirmationText');
        $this->farewell = TemplateId::create('farewell', ['hoursToExpire' => $this->emailUserRegistrationConfirmationExpire / 60 / 60]);
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
