<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\FileUpload;

use Common\Adapter\FileUpload\FileUploadSymfonyAdapter;
use Common\Domain\Exception\LogicException;
use Common\Domain\FileUpload\Exception\FileUploadCanNotWriteException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\FileUpload\Exception\FileUploadExtensionFileException;
use Common\Domain\FileUpload\Exception\FileUploadIniSizeException;
use Common\Domain\FileUpload\Exception\FileUploadNoFileException;
use Common\Domain\FileUpload\Exception\FileUploadPartialFileException;
use Common\Domain\FileUpload\Exception\FileUploadSizeException;
use Common\Domain\FileUpload\Exception\FileUploadTmpDirFileException;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;

class FileUploadSymfonyAdapterTest extends TestCase
{
    private MockObject|FileUploadSymfonyAdapter $object;
    private MockObject|UploadedFileInterface $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = $this->createMock(UploadedFileInterface::class);
        $this->object = $this->createPartialMock(FileUploadSymfonyAdapter::class, ['uniqid', 'slug']);
    }

    /** @test */
    public function istShouldThrowLogicExceptionGetingTheFileName(): void
    {
        $this->expectException(LogicException::class);

        $this->object->getFileName();
    }

    /** @test */
    public function istShouldGetingTheFileName(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $file = $this->createMock(UploadedFileInterface::class);
        $this->mock__invokeStubs($originalFileName, $safeFileName, $slugFileName, $pathToSaveFile, $uniqid, $file);

        $this->object->__invoke($this->file, $pathToSaveFile);
        $return = $this->object->getFileName();

        $this->assertSame($slugFileName, $return);
    }

    private function mock__invokeStubs(string $originalFileName, string $safeFileName, string $slugFileName, string $pathToSaveFile, string $uniqid, \Exception|UploadedFileInterface $moveReturn)
    {
        $this->file
            ->expects($this->once())
            ->method('getClientOriginalExtension')
            ->willReturn($originalFileName);

        $this->file
            ->expects($this->once())
            ->method('move')
            ->with($pathToSaveFile, $slugFileName)
            ->willReturnCallback(fn () => $moveReturn instanceof \Throwable ? throw $moveReturn : $moveReturn);
        $this->file
            ->expects($this->once())
            ->method('getClientOriginalExtension')
            ->willReturn($originalFileName);

        $this->file
            ->expects($this->once())
            ->method('guessExtension')
            ->willReturn(null);

        $this->object
            ->expects($this->once())
            ->method('uniqid')
            ->willReturn($uniqid);

        $this->object
            ->expects($this->once())
            ->method('slug')
            ->willReturn($safeFileName);
    }

    /** @test */
    public function itsouldUploadTheFile(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $file = $this->createMock(UploadedFileInterface::class);
        $this->mock__invokeStubs($originalFileName, $safeFileName, $slugFileName, $pathToSaveFile, $uniqid, $file);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadCanNotWriteException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new CannotWriteFileException()
        );

        $this->expectException(FileUploadCanNotWriteException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadExtensionFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new ExtensionFileException()
        );

        $this->expectException(FileUploadExtensionFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadSizeException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new FormSizeFileException()
        );

        $this->expectException(FileUploadSizeException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadIniSizeException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new IniSizeFileException()
        );

        $this->expectException(FileUploadIniSizeException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadNoFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new NoFileException()
        );

        $this->expectException(FileUploadNoFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadTmpDirFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new NoTmpDirFileException()
        );

        $this->expectException(FileUploadTmpDirFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadPartialFileException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new PartialFileException()
        );

        $this->expectException(FileUploadPartialFileException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }

    /** @test */
    public function itsouldThrowFileUploadException(): void
    {
        $pathToSaveFile = 'path/to/save/file';
        $originalFileName = 'file.txt';
        $safeFileName = 'safe_file';
        $uniqid = 'uniqid';
        $slugFileName = sprintf('%s-%s.%s', $safeFileName, $uniqid, null);

        $this->mock__invokeStubs(
            $originalFileName,
            $safeFileName,
            $slugFileName,
            $pathToSaveFile,
            $uniqid,
            new FileUploadException()
        );

        $this->expectException(FileUploadException::class);

        $this->object->__invoke($this->file, $pathToSaveFile);
    }
}
