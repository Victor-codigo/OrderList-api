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
     * @param string $fileNameToReplace Name of the file. File must be in "$pathToSaveFile" path.
     *
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileUploadReplaceException
     */
    public function __invoke(UploadedFileInterface $file, string $pathToSaveFile, string $fileNameToReplace = null): FileInterface;
}
