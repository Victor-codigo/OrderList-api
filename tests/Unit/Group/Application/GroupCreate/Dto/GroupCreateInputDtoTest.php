<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupCreate\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupCreate\Dto\GroupCreateInputDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';

class GroupCreateInputDtoTest extends TestCase
{
    private const string GROUP_ID = '452618d5-17fa-4c16-8825-f4f540fca822';
    private const string PATH_IMAGE_UPLOAD = 'tests/Fixtures/Files/Image.png';
    private const string PATH_FILE = 'tests/Fixtures/Files/file.txt';

    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
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
    public function itShouldValidateGroupTypeUser(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'this is a description of the group',
            'TYPE_USER',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateGroupTypeGroup(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'this is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            false
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldValidateDescriptionIsNull(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            null,
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            true
        );
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailNameIsNull(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            null,
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            true
        );
        $return = $object->validate($this->validator);

        $this->assertSame(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailNameIsWrong(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'Group Name-',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            true
        );
        $return = $object->validate($this->validator);

        $this->assertSame(['name' => [VALIDATION_ERRORS::ALPHANUMERIC_WITH_WHITESPACE]], $return);
    }

    #[Test]
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            str_pad('', 501, 'f'),
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            true
        );
        $return = $object->validate($this->validator);

        $this->assertSame(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    #[Test]
    public function itShouldFailGroupTypeIsWrong(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'WRONG_TYPE',
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            true
        );
        $return = $object->validate($this->validator);

        $this->assertSame(['type' => [VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupImageMimeTypeNotAllowed(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_OK),
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE]], $return);
    }

    #[Test]
    public function itShouldFailGroupImageSizeFormTooLarge(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_OK),
            true
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_IMAGE_TOO_LARGE]], $return);
    }

    #[Test]
    public function itShouldFailGroupImageSizeIniTooLarge(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_INI_SIZE),
            true
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE]], $return);
    }

    #[Test]
    public function itShouldFailGroupImageNoUploaded(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_FILE),
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    #[Test]
    public function itShouldFailGroupImagePartiallyUploaded(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_PARTIAL),
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL]], $return);
    }

    #[Test]
    public function itShouldFailGroupImageCantWrite(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_CANT_WRITE),
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE]], $return);
    }

    #[Test]
    public function itShouldFailGroupImageErrorExtension(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_EXTENSION),
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_EXTENSION]], $return);
    }

    #[Test]
    public function itShouldFailGroupImageErrorTmpDir(): void
    {
        $object = new GroupCreateInputDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            'GroupName',
            'This is a description of the group',
            'TYPE_GROUP',
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_NO_TMP_DIR),
            true
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_TMP_DIR]], $return);
    }
}
