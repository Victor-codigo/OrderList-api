<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Ports\FileUpload\FileInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class GroupImageTest extends TestCase
{
    private const string PATH_FILE = 'tests/Fixtures/Files/file.txt';
    private const string PATH_FILE_NOT_FOUND = 'tests/Fixtures/Files/fileNotFound.txt';
    private const string PATH_FILE_EMPTY = 'tests/Fixtures/Files/FileEmpty.txt';

    private GroupImage $object;
    private ValidationChain $validator;
    private MockObject|FileInterface $fileInterface;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->fileInterface = $this->getFileInterface(self::PATH_FILE);
        $this->object = new GroupImage($this->fileInterface);
        $this->validator = new ValidationChain();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$is_readable = null;
        BuiltInFunctionsReturn::$filesize = null;
        BuiltInFunctionsReturn::$getimagesize = null;
        BuiltInFunctionsReturn::$imagecreatefromstring = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    private function getFileInterface(string $fileName): MockObject|FileInterface
    {
        $file = $this
            ->getMockBuilder(File::class)
            ->setConstructorArgs([$fileName, false])
            ->getMock();

        $file
            ->expects($this->any())
            ->method('getPathname')
            ->willReturn($fileName);

        $fileInterface = $this
            ->getMockBuilder(FileInterface::class)
            ->onlyMethods(['getFile'])
            ->getMockForAbstractClass();

        $fileInterface
            ->expects($this->any())
            ->method('getFile')
            ->willReturn($file);

        return $fileInterface;
    }

    /** @test */
    public function itShouldValidateTheGroupImage(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];
        $object = new GroupImage($this->fileInterface);
        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateGroupImageIsNull(): void
    {
        $this->object = new GroupImage(null);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailFileMimeTypeCanNotBeTxt(): void
    {
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE], $return);
    }

    /** @test */
    public function itShouldFailFileNotFound(): void
    {
        $object = new GroupImage($this->getFileInterface(self::PATH_FILE_NOT_FOUND));

        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_FOUND], $return);
    }

    /** @test */
    public function itShouldFailFileIsNotReadable(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();
        BuiltInFunctionsReturn::$is_readable = false;

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_READABLE], $return);
    }

    /** @test */
    public function itShouldFailFileIsEmpty(): void
    {
        $fileInterface = $this->getFileInterface(self::PATH_FILE_EMPTY);
        /** @var MockObject|File $file */
        $file = $fileInterface->getFile();
        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        $object = new GroupImage($fileInterface);
        BuiltInFunctionsReturn::$is_readable = true;

        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_EMPTY], $return);
    }

    /** @test */
    public function itShouldFailFileSizeIsLargeThan2MB(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_LARGE], $return);
    }

    /** @test */
    public function itShouldValidateFileWidthHasNotMinWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();
        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFileWidthHasNotMaxWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFileHeighHasNotMinWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFileHeighHasNotMaxWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [1, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFilePixelsHasNotMinWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFilePixelsHasNotMaxWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFileAspectRatioHasNotMinWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFileAspectRatioHasNotMaxWidth(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFileCanBeAnSquare(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$filesize = 1;
        BuiltInFunctionsReturn::$getimagesize = [100, 100];

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateFileIsCorrupted(): void
    {
        /** @var MockObject|File $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$getimagesize = [100, 100];
        BuiltInFunctionsReturn::$imagecreatefromstring = false;

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldReturnNullAsAValidationValue(): void
    {
        $object = new GroupImage(null);
        $return = $object->getValidationValue();

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldReturnTheValidationValue(): void
    {
        $return = $this->object->getValidationValue();

        $this->assertInstanceOf(File::class, $return);
    }
}
