<?php

declare(strict_types=1);

namespace Product\Application\ProductCreate\Dto;

use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ProductCreateInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly NameWithSpaces $name;
    public readonly Description $description;
    public readonly ProductImage $image;

    public function __construct(UserShared $userSession, ?string $groupId, ?string $name, ?string $description, ?UploadedFileInterface $image)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->image = ValueObjectFactory::createProductImage($image);
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
        ]);
    }
}
