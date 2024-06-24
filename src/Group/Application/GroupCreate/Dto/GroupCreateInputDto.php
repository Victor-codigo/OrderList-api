<?php

declare(strict_types=1);

namespace Group\Application\GroupCreate\Dto;

use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\ValidationInterface;

class GroupCreateInputDto implements ServiceInputDtoInterface
{
    public readonly Identifier $userCreatorId;
    public readonly NameWithSpaces $name;
    public readonly Description $description;
    public readonly GroupType $type;
    public readonly GroupImage $image;
    public readonly bool $notify;

    public function __construct(Identifier $userCreatorId, ?string $name, ?string $description, ?string $type, ?UploadedFileInterface $image, ?bool $notify)
    {
        $this->userCreatorId = $userCreatorId;
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->type = ValueObjectFactory::createGroupType(GROUP_TYPE::tryFrom($type));
        $this->image = ValueObjectFactory::createGroupImage($image);
        $this->notify = $notify ?? true;
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'image' => $this->image,
        ]);
    }
}
