<?php

declare(strict_types=1);

namespace Common\Adapter\FileUpload;

use Common\Domain\FileUpload\Exception\FileUploadCanNotWriteException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\FileUpload\Exception\FileUploadExtensionFileException;
use Common\Domain\FileUpload\Exception\FileUploadIniSizeException;
use Common\Domain\FileUpload\Exception\FileUploadNoFileException;
use Common\Domain\FileUpload\Exception\FileUploadPartialFileException;
use Common\Domain\FileUpload\Exception\FileUploadSizeException;
use Common\Domain\FileUpload\Exception\FileUploadTmpDirFileException;
use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\Ports\FileUpload\FileInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileSymfonyAdapter extends FileSymfonyAdapter implements UploadedFileInterface
{
    /**
     * @var UploadedFile
     */
    protected File $file;

    public function __construct(UploadedFile $uploadedFile)
    {
        parent::__construct($uploadedFile);
    }

    /**
     * Returns the original file name.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then it should not be considered as a safe value.
     */
    #[\Override]
    public function getClientOriginalName(): string
    {
        return $this->file->getClientOriginalName();
    }

    /**
     * Returns the original file extension.
     *
     * It is extracted from the original file name that was uploaded.
     * Then it should not be considered as a safe value.
     */
    #[\Override]
    public function getClientOriginalExtension(): string
    {
        return $this->file->getClientOriginalExtension();
    }

    /**
     * Returns the file mime type.
     *
     * The client mime type is extracted from the request from which the file
     * was uploaded, so it should not be considered as a safe value.
     *
     * For a trusted mime type, use getMimeType() instead (which guesses the mime
     * type based on the file content).
     *
     * @see getMimeType()
     */
    #[\Override]
    public function getClientMimeType(): string
    {
        return $this->file->getClientMimeType();
    }

    /**
     * Returns the extension based on the client mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * This method uses the mime type as guessed by getClientMimeType()
     * to guess the file extension. As such, the extension returned
     * by this method cannot be trusted.
     *
     * For a trusted extension, use guessExtension() instead (which guesses
     * the extension based on the guessed mime type for the file).
     *
     * @see guessExtension()
     * @see getClientMimeType()
     */
    #[\Override]
    public function guessClientExtension(): ?string
    {
        return $this->file->guessClientExtension();
    }

    /**
     * Returns the upload error.
     *
     * If the upload was successful, the constant UPLOAD_ERR_OK is returned.
     * Otherwise one of the other UPLOAD_ERR_XXX constants is returned.
     */
    #[\Override]
    public function getError(): int
    {
        return $this->file->getError();
    }

    /**
     * Returns whether the file has been uploaded with HTTP and no error occurred.
     */
    #[\Override]
    public function isValid(): bool
    {
        return $this->file->isValid();
    }

    /**
     * Moves the file to a new location.
     *
     * @throws FileException if, for any reason, the file could not have been moved
     */
    #[\Override]
    public function move(string $directory, ?string $name = null): FileInterface
    {
        try {
            $fileSymfonyAdapter = new FileSymfonyAdapter($this->file);
            $fileSymfonyAdapter->move($directory, $name);

            return $fileSymfonyAdapter;
        } catch (CannotWriteFileException) {
            throw FileUploadCanNotWriteException::fromMessage('UPLOAD_ERR_CANT_WRITE error occurred');
        } catch (ExtensionFileException) {
            throw FileUploadExtensionFileException::fromMessage('UPLOAD_ERR_EXTENSION error occurred');
        } catch (FormSizeFileException) {
            throw FileUploadSizeException::fromMessage('UPLOAD_ERR_FORM_SIZE error occurred');
        } catch (IniSizeFileException) {
            throw FileUploadIniSizeException::fromMessage('UPLOAD_ERR_INI_SIZE error occurred');
        } catch (NoFileException) {
            throw FileUploadNoFileException::fromMessage('UPLOAD_ERR_NO_FILE error occurred');
        } catch (NoTmpDirFileException) {
            throw FileUploadTmpDirFileException::fromMessage('UPLOAD_ERR_NO_TMP_DIR error occurred');
        } catch (PartialFileException) {
            throw FileUploadPartialFileException::fromMessage('UPLOAD_ERR_PARTIAL error occurred');
        } catch (FileException $e) {
            throw FileUploadException::fromMessage($e->getMessage());
        }
    }

    /**
     * Returns the maximum size of an uploaded file as configured in php.ini.
     *
     * @return int|float The maximum size of an uploaded file in bytes (returns float if size > PHP_INT_MAX)
     */
    #[\Override]
    public static function getMaxFilesize(): int|float
    {
        return UploadedFile::getMaxFilesize();
    }
}
