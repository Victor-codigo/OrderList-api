<?php

declare(strict_types=1);

namespace User\Application\UserModify\Dto;

use Common\Domain\Model\ValueObject\Object\UserImage;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class UserModifyInputDto implements ServiceInputDtoInterface
{
    public readonly Email $email;
    public readonly NameWithSpaces $name;
    public readonly bool $imageRemove;
    public readonly ?UserImage $image;
    public readonly User $user;

    private function __construct(
        ?string $email,
        ?string $name,
        ?bool $imageRemove,
        ?UploadedFileInterface $image,
        User $user
    ) {
        $this->email = ValueObjectFactory::createEmail($email);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->imageRemove = $imageRemove ?? false;
        $this->image = ValueObjectFactory::createUserImage($image);
        $this->user = $user;
    }

    public static function create(
        ?string $email,
        ?string $name,
        ?bool $imageRemove,
        ?UploadedFileInterface $image,
        User $user
    ): self {
        return new self(
            $email,
            $name,
            $imageRemove,
            $image,
            $user
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'name' => $this->name,
            'image' => $this->image,
        ]);
    }
}
