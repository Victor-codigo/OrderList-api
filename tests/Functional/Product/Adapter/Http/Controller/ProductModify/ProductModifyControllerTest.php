<?php

declare(strict_types=1);

namespace Test\Functional\Product\Adapter\Http\Controller\ProductModify;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ProductModifyControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/products';
    private const string METHOD = 'PUT';
    private const string PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string USER_OTHER_GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private const string PATH_FIXTURES = __DIR__.'/Fixtures';
    private const string PATH_IMAGE_UPLOAD = __DIR__.'/Fixtures/Image.png';
    private const string PATH_IMAGE_BACKUP = 'tests/Fixtures/Files/Image.png';
    private const string PATH_IMAGE_NOT_ALLOWED = __DIR__.'/Fixtures/MimeTypeNotAllowed.txt';
    private const string PATH_IMAGE_NOT_ALLOWED_BACKUP = 'tests/Fixtures/Files/MimeTypeNotAllowed.txt';

    private string $pathImageProduct;

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

        $this->removeFolderFiles($this->pathImageProduct);
        rmdir($this->pathImageProduct);
    }

    private function createImageTestPath(): void
    {
        $this->pathImageProduct = static::getContainer()->getParameter('product.image.path');

        if (!file_exists($this->pathImageProduct)) {
            mkdir($this->pathImageProduct);
        }
    }

    private function getImageUploaded(string $path, string $originalName, string $mimeType, int $error): UploadedFile
    {
        return new UploadedFile($path, $originalName, $mimeType, $error, true);
    }

    #[Test]
    public function itShouldModifyTheProduct(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product modified', $responseContent->message);

        $this->assertSame(self::PRODUCT_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyTheProductNameIsTheSame(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Juanola',
                'description' => 'product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product modified', $responseContent->message);

        $this->assertSame(self::PRODUCT_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyTheProductNameDescriptionImageAreNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => null,
                'description' => null,
                'image_remove' => false,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product modified', $responseContent->message);

        $this->assertSame(self::PRODUCT_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldModifyTheProductImageRemoveIsTrue(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => null,
                'description' => null,
                'image_remove' => true,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Product modified', $responseContent->message);

        $this->assertSame(self::PRODUCT_ID, $responseContent->data->id);
    }

    #[Test]
    public function itShouldFailModifyingTheProductIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => null,
                'name' => 'Product name modified',
                'description' => 'product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->product_id);
    }

    #[Test]
    public function itShouldFailModifyingTheGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    #[Test]
    public function itShouldFailModifyingTheProductNameIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name wrong-',
                'description' => 'product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertSame(['alphanumeric_with_whitespace'], $responseContent->errors->name);
    }

    #[Test]
    public function itShouldFailModifyingTheProductNameIsRepeated(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Juan Carlos',
                'description' => 'product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_name_repeated'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product name repeated', $responseContent->message);

        $this->assertSame('Product name repeated', $responseContent->errors->product_name_repeated);
    }

    #[Test]
    public function itShouldFailModifyingTheProductDescriptionIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => str_pad('', 501, 'm'),
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['description'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertSame(['string_too_long'], $responseContent->errors->description);
    }

    #[Test]
    public function itShouldFailModifyingTheProductIdNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();

        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => '396e0152-d501-45d9-bf58-7498e11ea6c5',
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product not found', $responseContent->message);

        $this->assertSame('Product not found', $responseContent->errors->product_not_found);
    }

    #[Test]
    public function itShouldFailModifyingTheProductDoesNotBelongsGroupId(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['product_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Product not found', $responseContent->message);

        $this->assertSame('Product not found', $responseContent->errors->product_not_found);
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_NOT_ALLOWED, 'MimeTypeNotAllowed.txt', 'text/plain', UPLOAD_ERR_OK),
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_FORM_SIZE),
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_INI_SIZE),
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_FILE),
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_PARTIAL),
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_CANT_WRITE),
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_EXTENSION),
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
                'group_id' => self::USER_OTHER_GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_TMP_DIR),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['file_upload_no_tmp_dir'], $responseContent->errors->image);
    }

    #[Test]
    public function itShouldFailModifyingUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'product_id' => self::PRODUCT_ID,
                'name' => 'Product name modified',
                'description' => 'Product description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('User does not belong to the group', $responseContent->message);

        $this->assertSame('User does not belong to the group', $responseContent->errors->permissions);
    }
}
