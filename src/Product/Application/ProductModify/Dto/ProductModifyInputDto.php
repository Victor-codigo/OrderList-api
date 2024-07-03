<?php

declare(strict_types=1);

namespace Product\Application\ProductModify\Dto;

use Override;
use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ProductModifyInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $productId;
    public readonly Identifier $groupId;
    public readonly NameWithSpaces $name;
    public readonly Description $description;
    public readonly ProductImage $image;
    public readonly bool $imageRemove;

    public function __construct(
        UserShared $userSession,
        string|null $groupId,
        string|null $productId,
        string|null $name,
        string|null $description,
        UploadedFileInterface|null $image,
        bool|null $imageRemove
    ) {
        $this->userSession = $userSession;
        $this->productId = ValueObjectFactory::createIdentifier($productId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->image = ValueObjectFactory::createProductImage($image);
        $this->imageRemove = $imageRemove;
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        $valueObjects = [
            'product_id' => $this->productId,
            'group_id' => $this->groupId,
            'description' => $this->description,
            'image' => $this->image,
        ];

        if (!$this->name->isNull()) {
            $valueObjects['name'] = $this->name;
        }

        return $validator->validateValueObjectArray($valueObjects);
    }
}
