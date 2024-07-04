<?php

declare(strict_types=1);

namespace User\Application\UserModify;

use Common\Domain\Config\AppConfig;
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
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\Image\ImageInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserModify\Dto\UserModifyInputDto;
use User\Application\UserModify\Exception\UserModifyCanNotUploadFile;
use User\Domain\Port\Repository\UserRepositoryInterface;

class UserModifyUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validation,
        private UserRepositoryInterface $userRepository,
        private FileUploadInterface $fileUpload,
        private ImageInterface $image,
        private string $userImagePath
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
     * @throws ImageResizeException
     */
    private function userModify(UserModifyInputDto $userModifyDto): void
    {
        $userModifyDto->user->setName($userModifyDto->name);
        $profile = $userModifyDto->user->getProfile();

        if ($userModifyDto->imageRemove && $userModifyDto->image->isNull()) {
            $this->removeUserImage($profile->getImage());
            $profile->setImage(ValueObjectFactory::createPath(null));
        } elseif (!$userModifyDto->image->isNull()) {
            $fileName = $this->uploadUserImage($userModifyDto->image, $profile->getImage());
            $profile->setImage($fileName);
        }

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
     * @throws ImageResizeException
     */
    private function uploadUserImage(UserImage $image, Path $userCurrentFileName): Path
    {
        $uploadedFile = $image->getValue();
        $this->fileUpload->__invoke($uploadedFile, $this->userImagePath);
        $this->removeUserImage($userCurrentFileName);

        $this->image->resizeToAFrame(
            ValueObjectFactory::createPath("{$this->userImagePath}/{$this->fileUpload->getFileName()}"),
            AppConfig::USER_IMAGE_FRAME_SIZE_WIDTH,
            AppConfig::USER_IMAGE_FRAME_SIZE_HEIGHT
        );

        return ValueObjectFactory::createPath($this->fileUpload->getFileName());
    }

    /**
     * @throws DomainFileNotDeletedException
     */
    private function removeUserImage(Path $fileName): void
    {
        if ($fileName->isNull()) {
            return;
        }

        $file = $this->userImagePath.'/'.$fileName->getValue();

        if (!file_exists($file)) {
            return;
        }

        if (!unlink($file)) {
            throw DomainFileNotDeletedException::fromMessage(sprintf('File [%s] could not be deleted', $this->userImagePath.'/'.$fileName->getValue()));
        }
    }
}
