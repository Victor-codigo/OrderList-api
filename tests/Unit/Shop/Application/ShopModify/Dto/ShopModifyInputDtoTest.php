<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopModify\Dto;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopModify\Dto\ShopModifyInputDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class ShopModifyInputDtoTest extends TestCase
{
    private const string SHOP_ID = '9155b773-e59f-4b85-b5ca-f0fab1cdc613';
    private const string GROUP_ID = 'b31deb1b-2d2b-4846-9c2e-cf661ba4a51b';
    private const string PATH_FILE = 'tests/Fixtures/Files/file.txt';
    private const string PATH_IMAGE_UPLOAD = 'tests/Fixtures/Files/Image.png';

    private ValidationInterface $validator;
    private MockObject&UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
        $this->userSession = $this->createMock(UserShared::class);
    }

    #[\Override]
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

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateNameIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            null,
            'Shop address',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateAddressIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'shop name',
            null,
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateDescriptionImageAreNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            null,
            null,
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailShopIdIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            null,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailShopIdIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            'not valid id',
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['shop_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            null,
            'Shop name',
            'Shop address',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            'not a valid group id',
            'Shop name',
            'Shop address',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailNameIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name wrong-',
            'Shop address',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    #[Test]
    public function itShouldFailAddressIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name ',
            'Shop address *wrong*',
            'shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['address' => [VALIDATION_ERRORS::REGEX_FAIL]], $return);
    }

    #[Test]
    public function itShouldFailDescriptionIsNull(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            null,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailDescriptionIsWrong(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            str_pad('', 501, 'm'),
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    #[Test]
    public function itShouldFailImageMimeTypeNotAllowed(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'Shop description',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_OK),
            false
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE]], $return);
    }

    #[Test]
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_OK),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_IMAGE_TOO_LARGE]], $return);
    }

    #[Test]
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_INI_SIZE),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE]], $return);
    }

    #[Test]
    public function itShouldFailImageNoUploaded(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_NO_FILE),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    #[Test]
    public function itShouldFailImagePartiallyUploaded(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_PARTIAL),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL]], $return);
    }

    #[Test]
    public function itShouldFailImageCantWrite(): void
    {
        $object = new ShopModifyInputDto(
            $this->userSession,
            self::SHOP_ID,
            self::GROUP_ID,
            'Shop name',
            'Shop address',
            'Shop description',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_CANT_WRITE),
            false
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE]], $return);
    }
}
