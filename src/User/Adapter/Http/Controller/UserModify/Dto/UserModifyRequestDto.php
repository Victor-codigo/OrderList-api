<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserModify\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UserModifyRequestDto implements RequestDtoInterface
{
    public readonly ?string $name;
    public readonly ?bool $imageRemove;
    public readonly ?UploadedFile $image;

    public function __construct(Request $request)
    {
        $this->name = $request->request->get('name');
        $this->imageRemove = $request->request->getBoolean('image_remove');
        $this->image = $request->files->get('image');
    }
}
