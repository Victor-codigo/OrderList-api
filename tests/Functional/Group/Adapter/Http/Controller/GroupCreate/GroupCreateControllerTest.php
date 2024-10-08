<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupCreate;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupCreateControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/groups';
    private const string METHOD = 'POST';
    private const string PATH_IMAGE_UPLOAD = __DIR__.'/Fixtures/Image.png';
    private const string PATH_FIXTURES = __DIR__.'/Fixtures';
    private const string PATH_IMAGE_BACKUP = 'tests/Fixtures/Files/Image.png';
    private const string PATH_IMAGE_NOT_ALLOWED = __DIR__.'/Fixtures/MimeTypeNotAllowed.txt';
    private const string PATH_IMAGE_NOT_ALLOWED_BACKUP = 'tests/Fixtures/Files/MimeTypeNotAllowed.txt';
    private const string USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const string USER_HAS_NO_GROUP_PASSWORD = '123456';

    private string $patImageGroup;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(self::PATH_FIXTURES)) {
            mkdir(self::PATH_FIXTURES);
        }

        copy(self::PATH_IMAGE_BACKUP, self::PATH_IMAGE_UPLOAD);
        copy(self::PATH_IMAGE_NOT_ALLOWED_BACKUP, self::PATH_IMAGE_NOT_ALLOWED);
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removeFolderFiles($this->patImageGroup);
        rmdir($this->patImageGroup);
    }

    private function createImageTestPath(): void
    {
        $this->patImageGroup = static::getContainer()->getParameter('group.image.path');

        if (!file_exists($this->patImageGroup)) {
            mkdir($this->patImageGroup, 0777, true);
        }
    }

    private function getImageUpload(string $path, string $originalName, string $mimetype, int $error): UploadedFile
    {
        return new UploadedFile($path, $originalName, $mimetype, $error, true);
    }

    #[Test]
    public function itShouldCreateAGroupTypeGroup(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => str_pad('', 500, 'd'),
                'type' => 'TYPE_GROUP',
                'notify' => true,
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateAGroupTypeUser(): void
    {
        $client = $this->getNewClientAuthenticated(self::USER_HAS_NO_GROUP_EMAIL, self::USER_HAS_NO_GROUP_PASSWORD);
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => str_pad('', 500, 'd'),
                'type' => 'TYPE_USER',
                'notify' => false,
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateAGroupDescriptionIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNameOther',
                'description' => null,
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldCreateAGroupImageIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => str_pad('', 500, 'd'),
                'type' => 'TYPE_GROUP',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    #[Test]
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => str_pad('', 501, 'd'),
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['description'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['string_too_long'], $responseContent->errors->description);
    }

    #[Test]
    public function itShouldFailTypeGroupIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => 'Group description',
                'type' => 'WRONG_TYPE',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['type'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_null'], $responseContent->errors->type);
    }

    #[Test]
    public function itShouldFailUserAlreadyHasAUserGroup(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => 'Group description',
                'type' => 'TYPE_USER',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_type_user_repeated'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('User already has a group of type user', $responseContent->message);
        $this->assertEquals('User already has a group of type user', $responseContent->errors->group_type_user_repeated);
    }

    #[Test]
    public function itShouldFailNameIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => null,
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->name);
    }

    #[Test]
    public function itShouldFailNameIsTooShort(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => '',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'string_too_short'], $responseContent->errors->name);
    }

    #[Test]
    public function itShouldFailNameIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => str_pad('', 51, 'h'),
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['string_too_long'], $responseContent->errors->name);
    }

    #[Test]
    public function itShouldFailGroupNameAlreadyExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_name_repeated'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The group name already exists', $responseContent->message);
    }

    #[Test]
    public function itShouldFailNotPermission(): void
    {
        $client = $this->getNewClientAuthenticated('email@user.com', 'not allowed user');
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => str_pad('', 51, 'h'),
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    public function itShouldFailImageMimeTypeNotAllowed(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_NOT_ALLOWED, 'MimeTypeNotAllowed.txt', 'text/plain', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_invalid_mime_type'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_FORM_SIZE),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_form_size'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_INI_SIZE),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_init_size'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailImageNoUploaded(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_FILE),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_no_file'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailImagePartiallyUploaded(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_PARTIAL),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_partial'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailImageCantWrite(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_CANT_WRITE),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_cant_write'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailImageErrorExtension(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_EXTENSION),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_extension'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailImageErrorTmpDir(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNewOne',
                'description' => 'Group description',
                'type' => 'TYPE_GROUP',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_TMP_DIR),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_no_tmp_dir'], $responseContent->errors->image);
    }
}
