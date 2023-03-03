<?php

declare(strict_types=1);

namespace Group\Application\GroupModify\Dto;

use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileInterface;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class GroupModifyInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly Identifier $groupId;
    public readonly Name $name;
    public readonly Description $description;
    public readonly GroupImage $image;

    public function __construct(User $userSession, string|null $groupId, string|null $name, string|null $description, FileInterface|null $image)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createName($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->image = ValueObjectFactory::createGroupImage($image);
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
