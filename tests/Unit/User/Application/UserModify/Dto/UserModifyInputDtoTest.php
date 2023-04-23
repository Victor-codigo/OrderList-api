<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\UserPasswordChange\Dto;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;
use User\Application\UserModify\Dto\UserModifyInputDto;
use User\Domain\Model\User;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class UserModifyInputDtoTest extends TestCase
{
    private const PATH_IMAGE_UPLOAD = 'tests/Fixtures/Files/Image.png';
    private const PATH_FILE = 'tests/Fixtures/Files/file.txt';

    private ValidationChain $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$filesize = null;
        BuiltInFunctionsReturn::$getimagesize = null;
        BuiltInFunctionsReturn::$imagecreatefromstring = null;
        BuiltInFunctionsReturn::$is_readable = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    /** @test */
    public function itShouldBeValid(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldBeValidImageRemoveIsNull(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            null,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldBeValidImageRemoveIsTrue(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            true,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldBeValidImageIsNull(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            true,
            null,
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailNoName(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            null,
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNameIsTooShort(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            '',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT]], $return);
    }

    /** @test */
    public function itShouldFailNameIsTooLarge(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            str_pad('', 51, 'o'),
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNotAlphanumeric(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia-',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC]], $return);
    }

    /** @test */
    public function itShouldFailImageMimeTypeNotAllowed(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE]], $return);
    }

    /** @test */
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_OK, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_LARGE]], $return);
    }

    /** @test */
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_INI_SIZE, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE]], $return);
    }

    /** @test */
    public function itShouldFailImageNoUploaded(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_FILE, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    /** @test */
    public function itShouldFailImagePartilallyUploaded(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_PARTIAL, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL]], $return);
    }

    /** @test */
    public function itShouldFailImageCantWrite(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_CANT_WRITE, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE]], $return);
    }

    /** @test */
    public function itShouldFailImageErrorExtension(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_EXTENSION, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_EXTENSION]], $return);
    }

    /** @test */
    public function itShouldFailImageErrorTmpDir(): void
    {
        $object = UserModifyInputDto::create(
            'anastasia@hotmail.com',
            'Anastasia',
            false,
            new UploadedFileSymfonyAdapter(new UploadedFile(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_TMP_DIR, true)),
            User::fromPrimitives('id', 'Anastasia@hotmail.com', 'password', 'Anastasia', [USER_ROLES::USER])
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_TMP_DIR]], $return);
    }
}
