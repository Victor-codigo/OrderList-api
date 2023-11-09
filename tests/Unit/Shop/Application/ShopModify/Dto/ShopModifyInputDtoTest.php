<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopModify\Dto;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopModify\Dto\ShopModifyInputDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class ShopModifyInputDtoTest extends TestCase
{
    private const SHOP_ID = '9155b773-e59f-4b85-b5ca-f0fab1cdc613';
    private const GROUP_ID = 'b31deb1b-2d2b-4846-9c2e-cf661ba4a51b';
    private const PATH_FILE = 'tests/Fixtures/Files/file.txt';
    private const PATH_IMAGE_UPLOAD = 'tests/Fixtures/Files/Image.png';

    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userSession = $this->createMock(UserShared::class);
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

    private function getUploadedImage(string $path, string $originalName, string $mimeType, int $error): UploadedFileSymfonyAdapter
    {
        return new UploadedFileSymfonyAdapter(
            new UploadedFile($path, $originalName, $mimeType, $error, true)
        );
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateNameIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            null,
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDescriptionImageAreNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            null,
            null,
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailShopIdIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            null,
            self::GROUP_ID,
            'Shop name',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailShopIdIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            'not valid id',
            self::GROUP_ID,
            'Shop name',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            null,
            'Shop name',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            'not a valid group id',
            'Shop name',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNameIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name wrong-',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            null,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailDescriptionIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            str_pad('', 501, 'm'),
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailImageMimeTypeNotAllowed(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop description',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE]], $return);
    }

    /** @test */
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_OK),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_LARGE]], $return);
    }

    /** @test */
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_INI_SIZE),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE]], $return);
    }

    /** @test */
    public function itShouldFailImageNoUploaded(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_NO_FILE),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    /** @test */
    public function itShouldFailImagePartiallyUploaded(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_PARTIAL),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL]], $return);
    }

    /** @test */
    public function itShouldFailImageCantWrite(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_CANT_WRITE),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE]], $return);
    }
}
