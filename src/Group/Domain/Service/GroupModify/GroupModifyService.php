<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\FileSystem\DomainFileNotDeletedException;
use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupModify\Dto\GroupModifyDto;
use Group\Domain\Service\GroupModify\Exception\GroupModifyPermissionsException;

class GroupModifyService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private FileUploadInterface $fileUpload,
        private string $groupImagePath
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBConnectionException
     * @throws GroupModifyPermissionsException
     */
    public function __invoke(GroupModifyDto $input): void
    {
        $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId]);
        $this->isGroupModifiable($group[0]);

        $input->name->isNull() ?: $group[0]->setName($input->name);
        $input->description->isNull() ?: $group[0]->setDescription($input->description);
        $this->setGroupImage($group[0], $input->image, $input->imageRemove);

        $this->groupRepository->save($group[0]);
    }

    /**
     * @throws GroupModifyPermissionsException
     */
    private function isGroupModifiable(Group $group): void
    {
        if (GROUP_TYPE::USER === $group->getType()->getValue()) {
            throw new GroupModifyPermissionsException();
        }
    }

    private function setGroupImage(Group $group, GroupImage $image, bool $imageRemove): void
    {
        $fileUploadedName = $this->uploadGroupImage($image, $group->getImage());
        $fileUploadedName->isNull() ?: $group->setImage($fileUploadedName);

        if ($imageRemove && $fileUploadedName->isNull()) {
            $this->removeGroupImage($group->getImage());
            $group->setImage(ValueObjectFactory::createPath(null));
        }
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
    private function uploadGroupImage(GroupImage $groupImageUploaded, Path $userCurrentFileName): Path
    {
        if ($groupImageUploaded->isNull()) {
            return new Path(null);
        }

        $uploadedFile = $groupImageUploaded->getValue();
        $this->fileUpload->__invoke($uploadedFile, $this->groupImagePath);
        $this->removeGroupImage($userCurrentFileName);

        return new Path($this->fileUpload->getFileName());
    }

    /**
     * @throws DomainFileNotDeletedException
     */
    private function removeGroupImage(Path $fileName): void
    {
        if ($fileName->isNull()) {
            return;
        }

        $file = $this->groupImagePath.'/'.$fileName->getValue();

        if (!file_exists($file)) {
            return;
        }

        if (!unlink($file)) {
            throw DomainFileNotDeletedException::fromMessage(sprintf('File [%s] could not be deleted', $this->groupImagePath.'/'.$fileName->getValue()));
        }
    }
}
