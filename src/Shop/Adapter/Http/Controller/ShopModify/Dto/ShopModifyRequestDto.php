<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopModify\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ShopModifyRequestDto implements RequestDtoInterface
{
    public readonly ?string $shopId;
    public readonly ?string $groupId;
    public readonly ?string $name;
    public readonly ?string $address;
    public readonly ?string $description;
    public readonly ?UploadedFile $image;
    public readonly bool $imageRemove;

    public function __construct(Request $request)
    {
        $this->shopId = $request->request->get('shop_id');
        $this->groupId = $request->request->get('group_id');
        $this->name = $request->request->get('name');
        $this->address = $request->request->get('address');
        $this->description = $request->request->get('description');
        $this->image = $request->files->get('image');
        $this->imageRemove = $request->request->getBoolean('image_remove');
    }
}
