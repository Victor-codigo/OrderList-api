<?php

declare(strict_types=1);

namespace Common\Adapter\Event\Request;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\ValidationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestEventSubscriber implements EventSubscriberInterface
{
    private ValidationInterface $validator;

    public function __construct(ValidationInterface $validator)
    {
        $this->validator = $validator;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['__invoke', 101]];
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();
        $lang = ValueObjectFactory::createLanguage($request->query->get('lang'));
        $errorList = $this->validator->validateValueObject($lang);

        if (empty($errorList)) {
            $request->setLocale($lang->getValue());
        }
    }
}
