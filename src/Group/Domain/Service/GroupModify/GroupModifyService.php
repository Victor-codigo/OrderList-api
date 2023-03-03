<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupModify;

use Common\Domain\Exception\FileSystem\DomainFileNotDeletedException;
use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupModify\Dto\GroupModifyDto;

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
     */
    public function __invoke(GroupModifyDto $input): void
    {
        $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId]);
        $fileUploadedName = $this->uploadGroupImage($input->image, $group[0]->getImage());

        $group[0]
            ->setName($input->name)
            ->setDescription($input->description)
            ->setImage($fileUploadedName);

        $this->groupRepository->save($group[0]);
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
            return new path(null);
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
