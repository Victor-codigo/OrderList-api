<?php

declare(strict_types=1);

namespace Common\Adapter\FileUpload;

use Common\Domain\Exception\LogicException;
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
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadSymfonyAdapter implements FileUploadInterface
{
    private string $fileName;

    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    /**
     * @throws LogicException
     */
    public function getFileName(): string
    {
        if (!isset($this->fileName)) {
            throw LogicException::fromMessage('There is no file uploaded. Call method upload first');
        }

        return $this->fileName;
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
     */
    public function __invoke(UploadedFileInterface $file, string $pathToSaveFile): FileInterface
    {
        try {
            $this->fileName = $this->generateFileName($file);

            return $file->move($pathToSaveFile, $this->fileName);
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

    private function generateFileName(UploadedFileInterface $file): string
    {
        $originalFileName = pathinfo($file->getClientOriginalExtension(), PATHINFO_FILENAME);
        $safeFileName = $this->slug($originalFileName);

        return sprintf('%s-%s.%s', $safeFileName, $this->uniqid(), $file->guessExtension());
    }

    protected function uniqid(): string
    {
        return uniqid();
    }

    /**
     * This method exits, just because phpunit error: eval emits an error that shows in console all code of the class.
     */
    protected function slug(string $string, string $separator = '-', string|null $locale = null): string
    {
        return (string) $this->slugger->slug($string, $separator, $locale);
    }

    public function getNewInstance(): static
    {
        return new static($this->slugger);
    }
}
