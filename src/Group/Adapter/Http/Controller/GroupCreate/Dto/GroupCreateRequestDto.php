<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupCreate\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupCreateRequestDto implements RequestDtoInterface
{
    public readonly string|null $name;
    public readonly string|null $description;

    public function __construct(Request $request)
    {
        $this->name = $request->request->get('name');
        $this->description = $request->request->get('description');
    }
}
