<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\ProductCreate;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ProductCreateControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/products';
    private const METHOD = 'POST';
    private const PATH_IMAGE_UPLOAD = __DIR__.'/Fixtures/Image.png';
    private const PATH_FIXTURES = __DIR__.'/Fixtures';
    private const PATH_IMAGE_BACKUP = 'tests/Fixtures/Files/Image.png';
    private const PATH_IMAGE_NOT_ALLOWED = __DIR__.'/Fixtures/MimeTypeNotAllowed.txt';
    private const PATH_IMAGE_NOT_ALLOWED_BACKUP = 'tests/Fixtures/Files/MimeTypeNotAllowed.txt';
    private const PATH_IMAGES_GROUP_PUBLIC = 'public/assets/img/products';
    private const USER_HAS_NO_GROUP_EMAIL = 'email.other_2.active@host.com';
    private const USER_HAS_NO_GROUP_PASSWORD = '123456';
    private const GROUP_EXISTS_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const GROUP_ID_EXISTS_USER_NOT_BELONGS = '4d52266f-aa7e-324e-b92d-6152635dd09e';

    private string $pathImageProduct;

    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(self::PATH_FIXTURES)) {
            mkdir(self::PATH_FIXTURES);
        }

        copy(self::PATH_IMAGE_BACKUP, self::PATH_IMAGE_UPLOAD);
        copy(self::PATH_IMAGE_NOT_ALLOWED_BACKUP, self::PATH_IMAGE_NOT_ALLOWED);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removeFolderFiles($this->pathImageProduct);
        rmdir($this->pathImageProduct);
    }

    private function createImageTestPath(): void
    {
        $this->pathImageProduct = static::getContainer()->getParameter('product.image.path');

        if (!file_exists($this->pathImageProduct)) {
            mkdir($this->pathImageProduct, 0777, true);
        }
    }

    private function getImageUpload(string $path, string $originalName, string $mimetype, int $error): UploadedFile
    {
        return new UploadedFile($path, $originalName, $mimetype, $error, true);
    }

    /** @test */
    public function itShouldCreateAProduct(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'product name',
                'description' => 'product description',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldCreateAProductDescriptionIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'product name',
                'description' => null,
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldCreateAProductImageIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'product name',
                'description' => 'product description',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'product name',
                'description' => str_pad('', 501, 'd'),
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

    /** @test */
    public function itShouldFailProductGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'name' => 'product name',
                'description' => 'product description',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailProductGroupIdIsNotAValidUuid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'group id not wrong',
                'name' => 'product name',
                'description' => 'product description',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailProductGroupIdDoesNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => '6f4f22b3-08a6-44ca-a69e-2aae43651f98',
                'name' => 'product name',
                'description' => 'product description',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_error'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error validating the group', $responseContent->message);
        $this->assertEquals('Error validating the group', $responseContent->errors->group_error);
    }

    /** @test */
    public function itShouldFailProductGroupIdExistsButUserDoesNotBelongToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID_EXISTS_USER_NOT_BELONGS,
                'name' => 'product name',
                'description' => 'product description',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_error'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error validating the group', $responseContent->message);
        $this->assertEquals('Error validating the group', $responseContent->errors->group_error);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => null,
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailNameIsTooShort(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => '',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailNameIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => str_pad('', 51, 'h'),
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailGroupNameAlreadyExists22(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Maluela',
                'description' => 'product description',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name_repeated'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product name already exists', $responseContent->message);
    }

    /** @test */
    public function itShouldFailNotPermission(): void
    {
        $client = $this->getNewClientAuthenticated('email@user.com', 'not allowed user');
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
            ]),
            files: [
                'image' => $this->getImageUpload(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailImageMimeTypeNotAllowed(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailImageNoUploaded(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailImagePartiallyUploaded(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailImageCantWrite(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailImageErrorExtension(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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

    /** @test */
    public function itShouldFailImageErrorTmpDir(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_EXISTS_ID,
                'name' => 'Product name',
                'description' => 'product description',
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
