<?php

declare(strict_types=1);

namespace User\Adapter\Templates\Email\EmailPasswordRemember;

use Common\Domain\HtmlTemplate\TemplateDtoBase;
use Common\Domain\HtmlTemplate\TemplateId;

final class EmailPasswordRememberDto extends TemplateDtoBase
{
    public const TEMPLATE_PATH = 'Email/EmailPasswordRemember/EmailPasswordRemember.html.twig';
    public const TRANSLATOR_DOMAIN = 'EmailPasswordRemember';

    private string $appName;
    private string $userName;
    private string $urlPasswordRememberConfirm;
    private int $emailUserPasswordRememberExpireInSeconds;

    private TemplateId $title;
    private TemplateId $welcome;
    private TemplateId $buttonRestorationText;
    private TemplateId $farewell;

    public function setData(string $appName, string $userName, string $urlPasswordRememberConfirm, int $emailUserPasswordRememberExpireInSeconds): static
    {
        $this->appName = $appName;
        $this->userName = $userName;
        $this->urlPasswordRememberConfirm = $urlPasswordRememberConfirm;
        $this->emailUserPasswordRememberExpireInSeconds = $emailUserPasswordRememberExpireInSeconds;

        $this->setStaticData();

        return $this;
    }

    #[\Override]
    protected function setStaticData(): void
    {
        $this->title = TemplateId::create('title');
        $this->welcome = TemplateId::create('welcome', ['userName' => $this->userName]);
        $this->buttonRestorationText = TemplateId::create('buttonRestorationText');
        $this->farewell = TemplateId::create('farewell', ['hoursToExpire' => $this->emailUserPasswordRememberExpireInSeconds / 60 / 60]);
    }

    #[\Override]
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
