<?php

declare(strict_types=1);

namespace User\Application\UserModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Exception\FileSystem\DomainFileNotDeletedException;
use Common\Domain\FileUpload\Exception\FileUploadCanNotWriteException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\FileUpload\Exception\FileUploadExtensionFileException;
use Common\Domain\FileUpload\Exception\FileUploadIniSizeException;
use Common\Domain\FileUpload\Exception\FileUploadNoFileException;
use Common\Domain\FileUpload\Exception\FileUploadPartialFileException;
use Common\Domain\FileUpload\Exception\FileUploadTmpDirFileException;
use Common\Domain\Model\ValueObject\Object\UserImage;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserModify\Dto\UserModifyInputDto;
use User\Application\UserModify\Exception\UserModifyCanNotUploadFile;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Port\User\UserInterface;

class UserModifyService extends ServiceBase
{
    public function __construct(
        private UserInterface $userAdapter,
        private ValidationInterface $validation,
        private UserRepositoryInterface $userRepository,
        private FileUploadInterface $fileUpload,
        private string $userPublicImagePath
    ) {
    }

    public function __invoke(UserModifyInputDto $userModifyDto): void
    {
        try {
            $this->validation($userModifyDto);

            $this->userModify($userModifyDto);
        } catch (DBConnectionException|DomainFileNotDeletedException) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        } catch (FileUploadException) {
            throw UserModifyCanNotUploadFile::fromMessage('An error occurred while file was uploading');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(UserModifyInputDto $userDto): void
    {
        $errorList = $userDto->validate($this->validation);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
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
     * @throws DomainFileNotDeletedException
     */
    private function userModify(UserModifyInputDto $userModifyDto): void
    {
        $profile = $userModifyDto->user->getProfile();
        $fileName = $this->uploadUserImage($userModifyDto->image, $profile->getImage()->getValue());
        $profile->setImage($fileName);
        $userModifyDto->user->setName($userModifyDto->name);

        $this->userRepository->save($userModifyDto->user);
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
    private function uploadUserImage(UserImage $image, string|null $userCurrentFileName): Path
    {
        $uploadedFile = $image->getValue();
        $this->fileUpload->__invoke($uploadedFile, $this->userPublicImagePath);
        $this->removeUserImage($userCurrentFileName);

        return new Path($this->fileUpload->getFileName());
    }

    /**
     * @throws DomainFileNotDeletedException
     */
    private function removeUserImage(string|null $fileName): void
    {
        if (null === $fileName) {
            return;
        }

        $file = $this->userPublicImagePath.'/'.$fileName;

        if (!file_exists($file)) {
            return;
        }

        if (!unlink($file)) {
            throw DomainFileNotDeletedException::fromMessage(sprintf('File [%s] could not be deleted', $this->userPublicImagePath.'/'.$fileName));
        }
    }
}
