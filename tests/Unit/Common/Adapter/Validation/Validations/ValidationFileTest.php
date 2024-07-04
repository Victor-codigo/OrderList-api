<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Validations;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class ValidationFileTest extends TestCase
{
    private const string PATH_IMAGE = 'tests/Fixtures/Files/Image.png';
    private const string PATH_IMAGE_LANDSCAPE = 'tests/Fixtures/Files/Landscape.png';
    private const string PATH_IMAGE_PORTRAIT = 'tests/Fixtures/Files/Portrait.png';
    private const string PATH_IMAGE_EMPTY = 'tests/Fixtures/Files/FileEmpty.txt';

    private const array IMAGE_DATA = [
        'size' => 48_864,
        'mimeType' => 'image/png',
        'filenameMaxLength' => null,
        'widthMin' => 1024,
        'widthMax' => 1024,
        'heighMin' => 1024,
        'heighMax' => 1024,
        'pixelsMin' => 1_048_576,
        'pixelsMax' => 1_048_576,
        'aspectRatioMin' => 1,
        'aspectRatioMax' => 1,
    ];

    private const array LANDSCAPE_DATA = [
        'size' => 602_468,
        'mimeType' => 'image/png',
        'filenameMaxLength' => 50,
        'widthMin' => 776,
        'widthMax' => 776,
        'heighMin' => 436,
        'heighMax' => 436,
        'pixelsMin' => 338_336,
        'pixelsMax' => 338_336,
        'aspectRatioMin' => 1.779,
        'aspectRatioMax' => 1.779,
    ];

    private const array PORTRAIT_DATA = [
        'size' => 623_595,
        'mimeType' => 'image/png',
        'filenameMaxLength' => null,
        'widthMin' => 436,
        'widthMax' => 436,
        'heighMin' => 776,
        'heighMax' => 776,
        'pixelsMin' => 338_336,
        'pixelsMax' => 338_336,
        'aspectRatioMin' => 0.561,
        'aspectRatioMax' => 0.561,
    ];

    private ValidationInterface $object;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ValidationChain();
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

    /** @test */
    public function validationUserImageOk(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEmpty($return);
    }

    /** @test */
    public function validationUserImageFailMimeTypeWrong(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                ['image/bmp'],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE], $return);
    }

    /** @test */
    public function validationUserImageFailFilenameMaxLengthWrong(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                1,
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_NAME_TOO_LONG], $return);
    }

    /** @test */
    public function validationUserImageFailFileNotFound(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE.'-not-found', false))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_FOUND], $return);
    }

    /** @test */
    public function validationUserImageFailFileNotReadable(): void
    {
        BuiltInFunctionsReturn::$is_readable = false;
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_READABLE], $return);
    }

    /** @test */
    public function validationUserImageFailFileTooLarge(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                46_864,
                ['image/png'],
                self::IMAGE_DATA['filenameMaxLength'],
                1024,
                1024,
                1024,
                1024,
                1_048_576,
                1_048_576,
                1,
                1,
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_LARGE], $return);
    }

    /** @test */
    public function validationUserImageFailFileEmpty(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE_EMPTY))
            ->image(
                self::IMAGE_DATA['size'] - 1,
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_EMPTY], $return);
    }

    /** @test */
    public function validationUserImageFailFileTooNarrow(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'] + 1,
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_NARROW], $return);
    }

    /** @test */
    public function validationUserImageFailFileTooWide(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'] - 1,
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_WIDE], $return);
    }

    /** @test */
    public function validationUserImageFailFileTooLow(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'] + 1,
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_LOW], $return);
    }

    /** @test */
    public function validationUserImageFailFileTooHigh(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'] - 1,
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_HIGH], $return);
    }

    /** @test */
    public function validationUserImageFailFileTooFewPixels(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'] + 1,
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_FEW_PIXEL], $return);
    }

    /** @test */
    public function validationUserImageFailFileTooManyPixels(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'] - 1,
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_TOO_MANY_PIXEL], $return);
    }

    /** @test */
    public function validationUserImageFailFileRatioTooSmall(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'] + 0.1,
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_RATIO_TOO_SMALL], $return);
    }

    /** @test */
    public function validationUserImageFailFileRatioTooBig(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'] - 0.1,
                false,
                true,
                true,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_RATIO_TOO_BIG], $return);
    }

    /** @test */
    public function validationUserImageFailLandscapeNotAllowed(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE_LANDSCAPE))
            ->image(
                self::LANDSCAPE_DATA['size'],
                [self::LANDSCAPE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::LANDSCAPE_DATA['widthMin'],
                self::LANDSCAPE_DATA['widthMax'],
                self::LANDSCAPE_DATA['heighMin'],
                self::LANDSCAPE_DATA['heighMax'],
                self::LANDSCAPE_DATA['pixelsMin'],
                self::LANDSCAPE_DATA['pixelsMax'],
                self::LANDSCAPE_DATA['aspectRatioMin'],
                self::LANDSCAPE_DATA['aspectRatioMax'],
                false,
                false,
                false,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_LANDSCAPE_NOT_ALLOWED], $return);
    }

    /** @test */
    public function validationUserImageFailSquareNotAllowed(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->image(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::IMAGE_DATA['widthMin'],
                self::IMAGE_DATA['widthMax'],
                self::IMAGE_DATA['heighMin'],
                self::IMAGE_DATA['heighMax'],
                self::IMAGE_DATA['pixelsMin'],
                self::IMAGE_DATA['pixelsMax'],
                self::IMAGE_DATA['aspectRatioMin'],
                self::IMAGE_DATA['aspectRatioMax'],
                false,
                false,
                false,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_SQUARE_NOT_ALLOWED], $return);
    }

    /** @test */
    public function validationUserImageFailPortraitNotAllowed(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE_PORTRAIT))
            ->image(
                self::PORTRAIT_DATA['size'],
                [self::PORTRAIT_DATA['mimeType']],
                self::IMAGE_DATA['filenameMaxLength'],
                self::PORTRAIT_DATA['widthMin'],
                self::PORTRAIT_DATA['widthMax'],
                self::PORTRAIT_DATA['heighMin'],
                self::PORTRAIT_DATA['heighMax'],
                self::PORTRAIT_DATA['pixelsMin'],
                self::PORTRAIT_DATA['pixelsMax'],
                self::PORTRAIT_DATA['aspectRatioMin'],
                self::PORTRAIT_DATA['aspectRatioMax'],
                false,
                false,
                false,
                false
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_IMAGE_PORTRAIT_NOT_ALLOWED], $return);
    }

    /** @test */
    public function validationFileOk(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->file(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']]
            )
            ->validate();

        $this->assertEmpty($return);
    }

    /** @test */
    public function validationFileFailFileNotFound(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE.'-not-found', false))
            ->file(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']]
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_FOUND], $return);
    }

    /** @test */
    public function validationFileFailFileNotReadable(): void
    {
        BuiltInFunctionsReturn::$is_readable = false;
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->file(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']]
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_NOT_READABLE], $return);
    }

    /** @test */
    public function validationFileFailFileTooLarge(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE))
            ->file(
                self::IMAGE_DATA['size'] - 40000,
                [self::IMAGE_DATA['mimeType']]
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_TOO_LARGE], $return);
    }

    /** @test */
    public function validationFileFailFileEmpty(): void
    {
        $return = $this->object
            ->setValue(new File(self::PATH_IMAGE_EMPTY))
            ->file(
                self::IMAGE_DATA['size'],
                [self::IMAGE_DATA['mimeType']]
            )
            ->validate();

        $this->assertEquals([VALIDATION_ERRORS::FILE_EMPTY], $return);
    }
}
