<?php

declare(strict_types=1);

namespace Common\Domain\Service\Image\UploadImage;

use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\FileUpload\Exception\FileUploadCanNotWriteException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\FileUpload\Exception\FileUploadExtensionFileException;
use Common\Domain\FileUpload\Exception\FileUploadIniSizeException;
use Common\Domain\FileUpload\Exception\FileUploadNoFileException;
use Common\Domain\FileUpload\Exception\FileUploadPartialFileException;
use Common\Domain\FileUpload\Exception\FileUploadReplaceException;
use Common\Domain\FileUpload\Exception\FileUploadTmpDirFileException;
use Common\Domain\Model\ValueObject\Object\ObjectValueObject;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\Image\ImageInterface;
use Common\Domain\Service\Image\EntityImageModifyInterface;
use Common\Domain\Service\Image\UploadImage\Dto\UploadImageDto;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;

class UploadImageService
{
    public function __construct(
        private FileUploadInterface $fileUpload,
        private ImageInterface $image
    ) {
    }

    /**
     * @throws FileUploadReplaceException
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadReplaceException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws FileUploadReplaceException
     * @throws ImageResizeException
     */
    public function __invoke(UploadImageDto $input): Path
    {
        if ($input->remove) {
            return $this->entityRemoveImage($input->entity, $input->imagesPathToStore);
        }

        return $this->entitySetImage($input->entity, $input->imageUploaded, $input->imagesPathToStore, $input->resizeWidth, $input->resizeHeight);
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
     * @throws ImageResizeException
     */
    private function entitySetImage(EntityImageModifyInterface $entity, ObjectValueObject $imageUploaded, Path $imagesPathToStore, ?float $resizeWidth, ?float $resizeHeight): Path
    {
        if ($imageUploaded->isNull()) {
            return $entity->getImage();
        }

        $fileUploadedName = $this->imageUpload($entity->getImage(), $imageUploaded, $imagesPathToStore);
        $this->imageResize($fileUploadedName, $imagesPathToStore, $resizeWidth, $resizeHeight);
        $entity->setImage($fileUploadedName);

        return $fileUploadedName;
    }

    /**
     * @throws FileUploadReplaceException
     */
    private function entityRemoveImage(EntityImageModifyInterface $entity, Path $imagesPathToStore): Path
    {
        $image = new Path(null);
        $this->fileRemove($imagesPathToStore, $entity->getImage());
        $entity->setImage($image);

        return $image;
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
     */
    private function imageUpload(Path $imageCurrent, ObjectValueObject $imageUploaded, Path $pathImagesToStore): Path
    {
        $this->fileUpload->__invoke($imageUploaded->getValue(), $pathImagesToStore->getValue(), $imageCurrent->getValue());

        return new Path($this->fileUpload->getFileName());
    }

    /**
     * @throws ImageResizeException
     */
    private function imageResize(Path $image, Path $pathImagesToStore, ?float $resizeWidth, ?float $resizeHeight): void
    {
        if (null === $resizeWidth) {
            return;
        }

        if (null === $resizeHeight) {
            return;
        }

        $this->image->resizeToAFrame(
            ValueObjectFactory::createPath("{$pathImagesToStore->getValue()}/{$image->getValue()}"),
            $resizeWidth,
            $resizeHeight
        );
    }

    /**
     * @throws FileUploadReplaceException
     */
    private function fileRemove(Path $imagePath, Path $fileName): void
    {
        if ($fileName->isNull()) {
            return;
        }

        $file = "{$imagePath->getValue()}/{$fileName->getValue()}";

        if (!file_exists($file)) {
            return;
        }

        if (!unlink($file)) {
            throw FileUploadReplaceException::fromMessage(sprintf('File [%s] could not be Replaced', $file));
        }
    }
}
