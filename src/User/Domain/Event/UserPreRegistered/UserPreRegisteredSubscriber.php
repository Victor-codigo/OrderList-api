<?php

declare(strict_types=1);

namespace User\Domain\Event\UserPreRegistered;

use Common\Domain\Event\EventDomainSubscriberInterface;
use User\Domain\Service\SendEmailRegisterConfirm\Dto\SendEmailRegistrationConfirmInputDto;
use User\Domain\Service\SendEmailRegisterConfirm\SendEmailRegistrationConfirmationService;

class UserPreRegisteredSubscriber implements EventDomainSubscriberInterface
{
    private SendEmailRegistrationConfirmationService $sendEmailRegistrationConfirmationService;

    public function __construct(SendEmailRegistrationConfirmationService $sendEmailRegistrationConfirmationService)
    {
        $this->sendEmailRegistrationConfirmationService = $sendEmailRegistrationConfirmationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [UserPreRegisteredEvent::class];
    }

    public function __invoke(UserPreRegisteredEvent $event): void
    {
        $this->sendEmailRegistrationConfirmationService->__invoke(
            new SendEmailRegistrationConfirmInputDto(
                $event->id,
                $event->emailTo,
                $event->userRegisterEmailConfirmationUrl
            )
        );
    }
}
