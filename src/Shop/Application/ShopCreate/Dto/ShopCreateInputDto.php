<?php

declare(strict_types=1);

namespace Shop\Application\ShopCreate\Dto;

use Override;
use Common\Domain\Model\ValueObject\Object\ShopImage;
use Common\Domain\Model\ValueObject\String\Address;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ShopCreateInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly NameWithSpaces $name;
    public readonly Address $address;
    public readonly Description $description;
    public readonly ShopImage $image;

    public function __construct(UserShared $userSession, ?string $groupId, ?string $name, ?string $address, ?string $description, ?UploadedFileInterface $image)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->address = ValueObjectFactory::createAddress($address);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->image = ValueObjectFactory::createShopImage($image);
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
            'image' => $this->image,
        ]);
    }
}
