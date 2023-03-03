<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupCreate;

use Common\Domain\Exception\FileSystem\DomainFileNotDeletedException;
use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupCreate\Dto\GroupCreateDto;

class GroupCreateService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private FileUploadInterface $fileUpload,
        private string $groupImagePath
    ) {
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws DomainFileNotDeletedException
     */
    public function __invoke(GroupCreateDto $input): Group
    {
        $groupNew = $this->createGroup($input);
        $userGroup = $this->createUserGroup($groupNew->getId(), $input->userCreatorId, $groupNew);
        $groupNew->addUserGroup($userGroup);
        $this->groupRepository->save($groupNew);

        return $groupNew;
    }

    /**
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws DomainFileNotDeletedException
     */
    private function createGroup(GroupCreateDto $input): Group
    {
        $id = $this->groupRepository->generateId();

        return new Group(
            ValueObjectFactory::createIdentifier($id),
            $input->name,
            ValueObjectFactory::createGroupType(GROUP_TYPE::USER),
            $input->description,
            $this->uploadGroupImage($input->image)
        );
    }

    private function createUserGroup(Identifier $groupId, Identifier $userId, Group $group): UserGroup
    {
        return new UserGroup(
            $groupId,
            $userId,
            ValueObjectFactory::createRoles([ValueObjectFactory::createRol(GROUP_ROLES::ADMIN)]),
            $group
        );
    }

    /**
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws DomainFileNotDeletedException
     */
    private function uploadGroupImage(GroupImage $image): Path
    {
        if ($image->isNull()) {
            return new path(null);
        }

        $uploadedFile = $image->getValue();
        $this->fileUpload->__invoke($uploadedFile, $this->groupImagePath);

        return new Path($this->fileUpload->getFileName());
    }
}
