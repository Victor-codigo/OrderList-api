<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\Object;

use Override;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\Object\File;
use Common\Domain\Ports\FileUpload\FileInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class FileTest extends TestCase
{
    private const string PATH_FILE = 'tests/Fixtures/Files/file.txt';
    private const string PATH_FILE_NOT_FOUND = 'tests/Fixtures/Files/FileNotFound.txt';
    private const string PATH_FILE_EMPTY = 'tests/Fixtures/Files/FileEmpty.txt';

    private File $object;
    private ValidationChain $validator;
    private MockObject|FileInterface $fileInterface;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->fileInterface = $this->getFileInterface(self::PATH_FILE);
        $this->object = new File($this->fileInterface);
        $this->validator = new ValidationChain();
    }

    #[Override]
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
            ->getMockBuilder(SymfonyFile::class)
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
    public function itShouldValidate(): void
    {
        /** @var MockObject|SymfonyFile $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailFileCanNotBeNull(): void
    {
        $this->object = new File(null);
        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_NULL, VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    /** @test */
    public function itShouldFailFileNotFound(): void
    {
        $object = new File($this->getFileInterface(self::PATH_FILE_NOT_FOUND));

        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_FOUND], $return);
    }

    /** @test */
    public function itShouldFailFileIsNotReadable(): void
    {
        /** @var MockObject|SymfonyFile $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        BuiltInFunctionsReturn::$is_readable = false;

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_READABLE], $return);
    }

    /** @test */
    public function itShouldFailFileIsEmpty(): void
    {
        $fileInterface = $this->getFileInterface(self::PATH_FILE_EMPTY);
        /** @var MockObject|SymfonyFile $file */
        $file = $fileInterface->getFile();
        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        $object = new File($fileInterface);
        BuiltInFunctionsReturn::$is_readable = true;

        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_EMPTY], $return);
    }

    /** @test */
    public function itShouldFailFileSizeIsLargeThan2MB(): void
    {
        /** @var MockObject|SymfonyFile $file */
        $file = $this->fileInterface->getFile();

        $file
            ->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/png');

        $object = new File($file);
        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;

        $return = $this->validator->validateValueObject($this->object);

        $this->assertEquals([VALIDATION_ERRORS::FILE_TOO_LARGE], $return);
    }

    /** @test */
    public function itShouldReturnNullAsAValidationValue(): void
    {
        $object = new File(null);
        $return = $object->getValidationValue();

        $this->assertNull($return);
    }

    /** @test */
    public function itShouldReturnTheValidationValue(): void
    {
        $return = $this->object->getValidationValue();

        $this->assertInstanceOf(SymfonyFile::class, $return);
    }
}
