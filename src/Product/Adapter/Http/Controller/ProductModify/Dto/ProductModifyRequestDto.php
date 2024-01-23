<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductModify\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ProductModifyRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;
    public readonly string|null $productId;
    public readonly string|null $name;
    public readonly string|null $description;
    public readonly float|null $price;
    public readonly UploadedFile|null $image;
    public readonly bool $imageRemove;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->productId = $request->request->get('product_id');
        $this->name = $request->request->get('name');
        $this->description = $request->request->get('description');
        $this->image = $request->files->get('image');
        $this->imageRemove = $request->request->getBoolean('image_remove');
    }
}
