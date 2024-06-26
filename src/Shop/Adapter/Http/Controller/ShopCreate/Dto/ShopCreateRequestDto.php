<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopCreate\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ShopCreateRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupId;
    public readonly ?string $name;
    public readonly ?string $description;
    public readonly ?string $address;
    public readonly ?UploadedFile $image;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->name = $request->request->get('name');
        $this->description = $request->request->get('description');
        $this->address = $request->request->get('address');
        $this->image = $request->files->get('image');
    }
}
