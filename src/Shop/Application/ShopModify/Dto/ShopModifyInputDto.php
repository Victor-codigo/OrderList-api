<?php

declare(strict_types=1);

namespace Shop\Application\ShopModify\Dto;

use Common\Domain\Model\ValueObject\Object\ShopImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ShopModifyInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $shopId;
    public readonly Identifier $groupId;
    public readonly NameWithSpaces $name;
    public readonly Description $description;
    public readonly ShopImage $image;
    public readonly bool $imageRemove;

    public function __construct(UserShared $userSession, string|null $shopId, string|null $groupId, string|null $name, string|null $description, UploadedFileInterface|null $image, bool|null $imageRemove)
    {
        $this->userSession = $userSession;
        $this->shopId = ValueObjectFactory::createIdentifier($shopId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->image = ValueObjectFactory::createShopImage($image);
        $this->imageRemove = $imageRemove;
    }

    public function validate(ValidationInterface $validator): array
    {
        $valueObjects = [
            'shop_id' => $this->shopId,
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
