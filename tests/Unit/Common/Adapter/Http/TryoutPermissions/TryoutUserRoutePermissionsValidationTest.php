<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Http\TryoutPermissions;

use Common\Adapter\Http\TryoutPermissions\Exception\TryoutUserRoutePermissionsException;
use Common\Adapter\Http\TryoutPermissions\TryoutUserRoutePermissionsValidation;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\TestCase;

class TryoutUserRoutePermissionsValidationTest extends TestCase
{
    private const string USER_ID = 'dc59f65f-9ded-4240-ae16-ea28a3e8c607';
    private const string USER_TRY_OUT_ID = '503e5ef3-aa71-4ba3-a944-043cb342b0b5';
    private const string ROUTE_VALID = 'user_get';
    private const string ROUTE_NOT_VALID = 'route_not_valid';

    private TryoutUserRoutePermissionsValidation $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new TryoutUserRoutePermissionsValidation(self::USER_TRY_OUT_ID);
    }

    /** @test */
    public function itShouldValidateCurrentUserIsNotTryoutUser(): void
    {
        $this->expectNotToPerformAssertions();
        $userCurrentId = ValueObjectFactory::createIdentifier(self::USER_ID);

        $this->object->__invoke($userCurrentId, self::ROUTE_VALID);
    }

    /** @test */
    public function itShouldValidateCurrentUserIsTryoutUserRouteValid(): void
    {
        $this->expectNotToPerformAssertions();
        $userCurrentId = ValueObjectFactory::createIdentifier(self::USER_TRY_OUT_ID);

        $this->object->__invoke($userCurrentId, self::ROUTE_VALID);
    }

    /** @test */
    public function itShouldFailCurrentUserIsTryoutUserRouteNotValid(): void
    {
        $userCurrentId = ValueObjectFactory::createIdentifier(self::USER_TRY_OUT_ID);

        $this->expectException(TryoutUserRoutePermissionsException::class);
        $this->object->__invoke($userCurrentId, self::ROUTE_NOT_VALID);
    }
}
