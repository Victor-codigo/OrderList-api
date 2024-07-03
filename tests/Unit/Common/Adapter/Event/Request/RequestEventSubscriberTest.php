<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Request;

use Override;
use Common\Adapter\Event\Request\RequestEventSubscriber;
use Common\Adapter\Validation\ValidationChain;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestEventSubscriberTest extends TestCase
{
    private RequestEventSubscriber $object;
    private MockObject|RequestEvent $event;
    private MockObject|Request $request;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->event = $this->createMock(RequestEvent::class);
        $this->request = $this->createMock(Request::class);
        $this->object = new RequestEventSubscriber(new ValidationChain());
    }

    /** @test */
    public function itShouldSetLocaleAsSpanish(): void
    {
        $this->event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request
            ->expects($this->once())
            ->method('setLocale')
            ->with('es');

        $this->request->query = new InputBag(['lang' => 'es']);
        $this->object->__invoke($this->event);
    }

    /** @test */
    public function itShouldSetLocaleAsEnglish(): void
    {
        $this->event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request
            ->expects($this->once())
            ->method('setLocale')
            ->with('en');

        $this->request->query = new InputBag(['lang' => 'en']);
        $this->object->__invoke($this->event);
    }

    /** @test */
    public function itShouldChangeLocaleNoLangInQueryString(): void
    {
        $this->event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request
            ->expects($this->never())
            ->method('setLocale');

        $this->request->query = new InputBag([]);
        $this->object->__invoke($this->event);
    }

    /** @test */
    public function itShouldChangeLocaleWrongLang(): void
    {
        $this->event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request
            ->expects($this->never())
            ->method('setLocale');

        $this->request->query = new InputBag(['lang' => 'eng']);
        $this->object->__invoke($this->event);
    }
}
