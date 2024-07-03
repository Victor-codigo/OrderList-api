<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupCreate\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class GroupCreateRequestDto implements RequestDtoInterface
{
    public readonly ?string $name;
    public readonly ?string $description;
    public readonly ?string $type;
    public readonly ?UploadedFile $image;
    public readonly bool $notify;

    public function __construct(Request $request)
    {
        $this->name = $request->request->get('name');
        $this->description = $request->request->get('description');
        $this->type = $request->request->get('type');
        $this->image = $request->files->get('image');
        $this->notify = $request->request->getBoolean('notify', true);
    }
}
