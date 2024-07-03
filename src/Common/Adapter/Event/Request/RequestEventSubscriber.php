<?php

declare(strict_types=1);

namespace Common\Adapter\Event\Request;

use Common\Adapter\Http\TryoutPermissions\Exception\TryoutUserRoutePermissionsException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\ValidationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ValidationInterface $validator,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['__invoke', 101]];
    }

    /**
     * @throws TryoutUserRoutePermissionsException
     */
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $this->setLocale($request);
    }

    private function setLocale(Request $request): void
    {
        $lang = ValueObjectFactory::createLanguage($request->query->get('lang'));
        $errorList = $this->validator->validateValueObject($lang);

        if (empty($errorList)) {
            $request->setLocale($lang->getValue());
        }
    }
}
