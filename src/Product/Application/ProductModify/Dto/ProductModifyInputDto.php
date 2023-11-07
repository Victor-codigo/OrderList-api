<?php

declare(strict_types=1);

namespace Product\Application\ProductModify\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
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
    public readonly Identifier $shopId;
    public readonly NameWithSpaces $name;
    public readonly Description $description;
    public readonly Money $price;
    public readonly ProductImage $image;
    public readonly bool $imageRemove;

    public function __construct(
        UserShared $userSession,
        string|null $productId,
        string|null $groupId,
        string|null $shopId,
        string|null $name,
        string|null $description,
        float|null $price,
        UploadedFileInterface|null $image,
        bool|null $imageRemove
    ) {
        $this->userSession = $userSession;
        $this->productId = ValueObjectFactory::createIdentifier($productId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->shopId = ValueObjectFactory::createIdentifier($shopId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->price = ValueObjectFactory::createMoney($price);
        $this->image = ValueObjectFactory::createProductImage($image);
        $this->imageRemove = $imageRemove;
    }

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

        if (!$this->shopId->isNull()) {
            $valueObjects['shop_id'] = $this->shopId;
        }

        if (!$this->price->isNull()) {
            $valueObjects['price'] = $this->price;
        }

        return $validator->validateValueObjectArray($valueObjects);
    }
}
