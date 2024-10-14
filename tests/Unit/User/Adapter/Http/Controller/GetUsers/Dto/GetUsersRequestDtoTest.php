<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Http\Controller\GetUsers\Dto;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use User\Adapter\Http\Controller\GetUsers\Dto\GetUsersRequestDto;

class GetUsersRequestDtoTest extends TestCase
{
    private const string USER_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const int USERS_NUM_MAX = 100;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param array<string|int, string>|null $attributes
     */
    private function createRequest(?array $attributes): GetUsersRequestDto
    {
        if (null === $attributes) {
            $attributes = [];
        } else {
            $attributes = ['users_id' => implode(',', $attributes)];
        }

        $request = new Request(attributes: $attributes);

        return new GetUsersRequestDto($request);
    }

    #[Test]
    public function itShouldProcessAllIds(): void
    {
        $requestDto = $this->createRequest(array_fill(0, self::USERS_NUM_MAX, self::USER_ID));

        $this->assertCount(self::USERS_NUM_MAX, $requestDto->usersId);
    }

    #[Test]
    public function itShouldProcessOnlyTheMaximum(): void
    {
        $requestDto = $this->createRequest(array_fill(0, self::USERS_NUM_MAX + 1, self::USER_ID));

        $this->assertCount(self::USERS_NUM_MAX, $requestDto->usersId);
    }

    #[Test]
    public function itShouldProcessNotUsersSent(): void
    {
        $requestDto = $this->createRequest(null);

        $this->assertNull($requestDto->usersId);
    }
}
