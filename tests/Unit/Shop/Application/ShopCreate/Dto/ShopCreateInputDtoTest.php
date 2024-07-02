<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopCreate\Dto;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopCreate\Dto\ShopCreateInputDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class ShopCreateInputDtoTest extends TestCase
{
    private const string GROUP_ID = '046030fa-cbc9-4751-b277-40622e94eda3';
    private const string PATH_IMAGE_UPLOAD = 'tests/Fixtures/Files/Image.png';
    private const string PATH_FILE = 'tests/Fixtures/Files/file.txt';

    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetBuiltInFunctions();
        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    protected function tearDown(): void
    {
        $this->resetBuiltInFunctions();
    }

    private function resetBuiltInFunctions(): void
    {
        BuiltInFunctionsReturn::$is_readable = null;
        BuiltInFunctionsReturn::$filesize = null;
        BuiltInFunctionsReturn::$getimagesize = null;
        BuiltInFunctionsReturn::$unlink = null;
        BuiltInFunctionsReturn::$imagecreatefromstring = null;
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
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $groupId = null;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $groupId = 'group id wrong';
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $name = null;
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNameIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop-name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailAddressIsWrong(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address wrong &';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['address' => [VALIDATION_ERRORS::REGEX_FAIL]], $return);
    }

    /** @test */
    public function itShouldFailAddressIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = null;
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailDescriptionWrong(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = str_pad('', 501, 'f');
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = null;
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailImageWrong(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_FILE);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    /** @test */
    public function itShouldFailImageIsNull(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = null;
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailAllParamsAreWrong(): void
    {
        $groupId = 'group id wrong';
        $name = 'shop-name';
        $address = 'shop address';
        $description = str_pad('', 501, 'f');
        $shopImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_FILE);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals([
            'group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS],
            'name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE],
            'description' => [VALIDATION_ERRORS::STRING_TOO_LONG],
            'image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE],
        ],
            $return
        );
    }

    /** @test */
    public function itShouldFailShopImageMimeTypeNotAllowed(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE]], $return);
    }

    /** @test */
    public function itShouldFailShopImageSizeFormTooLarge(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_IMAGE_TOO_LARGE]], $return);
    }

    /** @test */
    public function itShouldFailShopImageSizeIniTooLarge(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_INI_SIZE);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE]], $return);
    }

    /** @test */
    public function itShouldFailShopImageNoUploaded(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_FILE);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    /** @test */
    public function itShouldFailShopImagePartiallyUploaded(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_PARTIAL);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL]], $return);
    }

    /** @test */
    public function itShouldFailShopImageCantWrite(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_CANT_WRITE);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE]], $return);
    }

    /** @test */
    public function itShouldFailShopImageErrorExtension(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_EXTENSION);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_EXTENSION]], $return);
    }

    /** @test */
    public function itShouldFailShopImageErrorTmpDir(): void
    {
        $groupId = self::GROUP_ID;
        $name = 'shop name';
        $address = 'shop address';
        $description = 'shop description';
        $shopImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_TMP_DIR);
        $object = new ShopCreateInputDto($this->userSession, $groupId, $name, $address, $description, $shopImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_TMP_DIR]], $return);
    }
}
