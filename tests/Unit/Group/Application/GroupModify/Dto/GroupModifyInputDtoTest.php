<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupModify\Dto;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\User\USER_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupModify\Dto\GroupModifyInputDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\BuiltInFunctionsReturn;

require_once 'tests/BuiltinFunctions/SymfonyComponentValidatorConstraints.php';
class GroupModifyInputDtoTest extends TestCase
{
    private const GROUP_USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const PATH_FILE = 'tests/Fixtures/Files/file.txt';
    private const PATH_IMAGE_UPLOAD = 'tests/Fixtures/Files/Image.png';

    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$is_readable = null;
        BuiltInFunctionsReturn::$filesize = null;
        BuiltInFunctionsReturn::$getimagesize = null;
        BuiltInFunctionsReturn::$unlink = null;
        BuiltInFunctionsReturn::$imagecreatefromstring = null;
    }

    private function getUser(): UserShared
    {
        return UserShared::fromPrimitives(self::GROUP_USER_ID, 'email@domain.com', 'UserName', [USER_ROLES::USER], null, new \DateTime());
    }

    private function getUploadedImage(string $path, string $originalName, string $mimeType, int $error): UploadedFileSymfonyAdapter
    {
        return new UploadedFileSymfonyAdapter(
            new UploadedFile($path, $originalName, $mimeType, $error, true)
        );
    }

    /** @test */
    public function itShouldValidateTheInput(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateTheInputImageRemoveIsTrue(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            true,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateDescriptionIsNull(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            null,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldValidateImageIsNull(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            null,
            false,
            null
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            null,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            'group id not valid',
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $user = $this->getUser();
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            null,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailNameIsNotValid(): void
    {
        $user = $this->getUser();
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            'not valid name',
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['name' => [VALIDATION_ERRORS::ALPHANUMERIC]], $return);
    }

    /** @test */
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = str_pad('', 501, 'p');
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'Image.png', 'image/png', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['description' => [VALIDATION_ERRORS::STRING_TOO_LONG]], $return);
    }

    /** @test */
    public function itShouldFailImageMimeTypeNotAllowed22(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_FILE, 'file.txt', 'text/plain', UPLOAD_ERR_OK)
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE]], $return);
    }

    /** @test */
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_OK)
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_LARGE]], $return);
    }

    /** @test */
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_INI_SIZE)
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE]], $return);
    }

    /** @test */
    public function itShouldFailImageNoUploaded(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_NO_FILE)
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE]], $return);
    }

    /** @test */
    public function itShouldFailImagePartiallyUploaded(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_PARTIAL)
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL]], $return);
    }

    /** @test */
    public function itShouldFailImageCantWrite(): void
    {
        $user = $this->getUser();
        $userNameModify = 'UserNameModified';
        $userDescriptionModify = 'User description modify';
        $object = new GroupModifyInputDto(
            $user,
            self::GROUP_ID,
            $userNameModify,
            $userDescriptionModify,
            false,
            $this->getUploadedImage(self::PATH_IMAGE_UPLOAD, 'file.txt', 'text/plain', UPLOAD_ERR_CANT_WRITE)
        );

        BuiltInFunctionsReturn::$filesize = 2 * 1_000_000 + 1;
        $return = $object->validate($this->validator);

        $this->assertEquals(['image' => [VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE]], $return);
    }
}
