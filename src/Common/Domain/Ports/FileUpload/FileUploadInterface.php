<?php

declare(strict_types=1);

namespace Common\Domain\Ports\FileUpload;

interface FileUploadInterface
{
    /**
     * @throws LogicException
     */
    public function getFileName(): string;

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
    public function __invoke(UploadedFileInterface $file, string $pathToSaveFile): FileInterface;
}
