<?php

declare(strict_types=1);

namespace Test\Unit\Group\Adapter\Http\Controller\GroupUserAdd\Dto;

use Group\Adapter\Http\Controller\GroupUserAdd\Dto\GroupUserAddRequestDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class GroupUserAddRequestDtoTest extends TestCase
{
    private const string USER_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const int USERS_NUM_MAX = 50;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param array<int, string|bool>|null $attributes
     */
    private function createRequest(?array $attributes): GroupUserAddRequestDto
    {
        $requestAttributes = [
            'group_id' => null,
            'users' => null,
            'admin' => null,
        ];

        if (null !== $attributes) {
            $requestAttributes['users'] = $attributes;
        }

        $request = new Request();
        $request->request = new InputBag($requestAttributes);

        return new GroupUserAddRequestDto($request);
    }

    #[Test]
    public function itShouldProcessAllIds(): void
    {
        $requestDto = $this->createRequest(array_fill(0, self::USERS_NUM_MAX, self::USER_ID));

        $this->assertCount(self::USERS_NUM_MAX, $requestDto->users);
    }

    #[Test]
    public function itShouldProcessOnlyTheMaximum(): void
    {
        $requestDto = $this->createRequest(array_fill(0, self::USERS_NUM_MAX + 1, self::USER_ID));

        $this->assertCount(self::USERS_NUM_MAX, $requestDto->users);
    }

    #[Test]
    public function itShouldProcessNotUsersSent(): void
    {
        $requestDto = $this->createRequest(null);

        $this->assertEmpty($requestDto->users);
    }
}
