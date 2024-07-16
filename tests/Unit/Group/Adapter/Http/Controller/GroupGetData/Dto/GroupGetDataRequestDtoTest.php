<?php

declare(strict_types=1);

namespace Test\Unit\Group\Adapter\Http\Controller\GroupGetData\Dto;

use Group\Adapter\Http\Controller\GroupGetData\Dto\GroupGetDataRequestDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class GroupGetDataRequestDtoTest extends TestCase
{
    private const string GROUP_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const int GROUP_NUM_MAX = 50;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createRequest(?array $attributes): GroupGetDataRequestDto
    {
        $requestAttributes = [
            'groups_id' => null,
        ];

        if (null !== $attributes) {
            $requestAttributes['groups_id'] = implode(',', $attributes);
        }

        $request = new Request();
        $request->attributes = new ParameterBag();
        $request->attributes->set('groups_id', $requestAttributes['groups_id']);

        return new GroupGetDataRequestDto($request);
    }

    /** @test */
    public function itShouldProcessAllIds(): void
    {
        $requestDto = $this->createRequest(array_fill(0, self::GROUP_NUM_MAX, self::GROUP_ID));

        $this->assertCount(self::GROUP_NUM_MAX, $requestDto->groupsId);
    }

    /** @test */
    public function itShouldProcessOnlyTheMaximum(): void
    {
        $requestDto = $this->createRequest(array_fill(0, self::GROUP_NUM_MAX + 1, self::GROUP_ID));

        $this->assertCount(self::GROUP_NUM_MAX, $requestDto->groupsId);
    }

    /** @test */
    public function itShouldProcessNotUsersSent(): void
    {
        $requestDto = $this->createRequest(null);

        $this->assertNull($requestDto->groupsId);
    }
}
