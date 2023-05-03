<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\Dto;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Application\ProductCreate\Dto\ProductCreateInputDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class ProductCreateInputDtoTest extends TestCase
{
    private const GROUP_ID = '046030fa-cbc9-4751-b277-40622e94eda3';
    private const PATH_IMAGE_UPLOAD = 'tests/Fixtures/Files/Image.png';
    private const PATH_FILE = 'tests/Fixtures/Files/file.txt';

    private ValidationInterface $validator;
    private MockObject|UploadedFileInterface $productImageFile;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productImageFile = $this->createMock(UploadedFileInterface::class);
        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
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
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $userSessionId = 'user session id';
        $groupId = null;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $userSessionId = 'user session id';
        $groupId = 'group id wrong';
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = null;
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNameIsWrong(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product-name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionWrong(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = str_pad('', 501, 'f');
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionIsNull(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = null;
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailImageWrong(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_FILE);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    /** @test */
    public function itShouldFailImageIsNull(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = null;
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailAllParamsAreWrong(): void
    {
        $userSessionId = 'user session id';
        $groupId = 'group id wrong';
        $name = 'product-name';
        $description = str_pad('', 501, 'f');
        $productImageFile = $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_FILE);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

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
    public function itShouldFailProductImageMimeTypeNotAllowed(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE]], $return);
    }

    /** @test */
    public function itShouldFailProductImageSizeFormTooLarge(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'image.png', 'image/png', UPLOAD_ERR_OK);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_LARGE]], $return);
    }

    /** @test */
    public function itShouldFailProductImageSizeIniTooLarge(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_INI_SIZE);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE]], $return);
    }

    /** @test */
    public function itShouldFailProductImageNoUploaded(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_FILE);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    /** @test */
    public function itShouldFailProductImagePartiallyUploaded(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_PARTIAL);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL]], $return);
    }

    /** @test */
    public function itShouldFailProductImageCantWrite(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_CANT_WRITE);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE]], $return);
    }

    /** @test */
    public function itShouldFailProductImageErrorExtension(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_EXTENSION);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_EXTENSION]], $return);
    }

    /** @test */
    public function itShouldFailProductImageErrorTmpDir(): void
    {
        $userSessionId = 'user session id';
        $groupId = self::GROUP_ID;
        $name = 'product name';
        $description = 'product description';
        $productImageFile = $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_TMP_DIR);
        $object = new ProductCreateInputDto($this->userSession, $groupId, $name, $description, $productImageFile);

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_TMP_DIR]], $return);
    }
}
