<?php

declare(strict_types=1);

namespace Product\Application\ProductCreate\Dto;

use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class ProductCreateInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly Identifier $groupId;
    public readonly NameWithSpaces $name;
    public readonly Description $description;
    public readonly ProductImage $image;

    public function __construct(User $userSession, string|null $groupId, string|null $name, string|null $description, UploadedFileInterface|null $image)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->image = ValueObjectFactory::createProductImage($image);
    }

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
