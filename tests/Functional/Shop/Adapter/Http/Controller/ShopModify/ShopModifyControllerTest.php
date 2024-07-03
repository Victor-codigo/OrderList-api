<?php

declare(strict_types=1);

namespace Test\Functional\Shop\Adapter\Http\Controller\ShopModify;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ShopModifyControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/shops';
    private const string METHOD = 'PUT';
    private const string SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string USER_OTHER_GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private const string PATH_FIXTURES = __DIR__.'/Fixtures';
    private const string PATH_IMAGE_UPLOAD = __DIR__.'/Fixtures/Image.png';
    private const string PATH_IMAGE_BACKUP = 'tests/Fixtures/Files/Image.png';
    private const string PATH_IMAGE_NOT_ALLOWED = __DIR__.'/Fixtures/MimeTypeNotAllowed.txt';
    private const string PATH_IMAGE_NOT_ALLOWED_BACKUP = 'tests/Fixtures/Files/MimeTypeNotAllowed.txt';

    private string $pathImageShop;

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

        $this->removeFolderFiles($this->pathImageShop);
        rmdir($this->pathImageShop);
    }

    private function createImageTestPath(): void
    {
        $this->pathImageShop = static::getContainer()->getParameter('shop.image.path');

        if (!file_exists($this->pathImageShop)) {
            mkdir($this->pathImageShop);
        }
    }

    private function getImageUploaded(string $path, string $originalName, string $mimeType, int $error): UploadedFile
    {
        return new UploadedFile($path, $originalName, $mimeType, $error, true);
    }

    /** @test */
    public function itShouldModifyTheShop(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop description modified',
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
        $this->assertSame('Shop modified', $responseContent->message);

        $this->assertSame(self::SHOP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldModifyTheShopNameIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => null,
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
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
        $this->assertSame('Shop modified', $responseContent->message);

        $this->assertSame(self::SHOP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldModifyTheShopAddressIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => null,
                'description' => 'Shop description modified',
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
        $this->assertSame('Shop modified', $responseContent->message);

        $this->assertSame(self::SHOP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldModifyTheShopDescriptionIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => null,
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
        $this->assertSame('Shop modified', $responseContent->message);

        $this->assertSame(self::SHOP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldModifyTheShopImageIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
                'image_remove' => false,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shop modified', $responseContent->message);

        $this->assertSame(self::SHOP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldModifyTheShopImageRemoveIsTrue(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
                'image_remove' => true,
            ]),
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Shop modified', $responseContent->message);

        $this->assertSame(self::SHOP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailModifyingTheShopIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => null,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->shop_id);
    }

    /** @test */
    public function itShouldFailModifyingTheGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => null,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
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

    /** @test */
    public function itShouldFailModifyingTheShopNameIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name wrong-',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
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

    /** @test */
    public function itShouldFailModifyingTheShopAddressIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop *address* modified',
                'description' => 'Shop description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['address'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertSame(['regex_fail'], $responseContent->errors->address);
    }

    /** @test */
    public function itShouldFailModifyingTheShopDescriptionIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
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

    /** @test */
    public function itShouldFailModifyingTheShopIdNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => '396e0152-d501-45d9-bf58-7498e11ea6c5',
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The shop id not found', $responseContent->message);

        $this->assertSame('The shop id not found', $responseContent->errors->shop_not_found);
    }

    /** @test */
    public function itShouldFailModifyingTheShopDoesNotBelongsGroupId(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::USER_OTHER_GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The shop id not found', $responseContent->message);

        $this->assertSame('The shop id not found', $responseContent->errors->shop_not_found);
    }

    /** @test */
    public function itShouldFailModifyingTheShopNameIsAlreadyInUse(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name 4',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
                'image_remove' => false,
            ]),
            files: [
                'image' => $this->getImageUploaded(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK),
            ]
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['shop_name_repeated'], Response::HTTP_CONFLICT);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Shop name is already in use', $responseContent->message);

        $this->assertSame('Shop name is already in use', $responseContent->errors->shop_name_repeated);
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
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailImageNoUploaded(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailImagePartiallyUploaded(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailImageCantWrite(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailImageErrorExtension(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailImageErrorTmpDir(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name modified',
                'address' => 'Shop address modified',
                'description' => 'shop name modified',
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

    /** @test */
    public function itShouldFailModifyingUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $this->createImageTestPath();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'shop_id' => self::SHOP_ID,
                'group_id' => self::GROUP_ID,
                'name' => 'Shop name 4',
                'address' => 'Shop address modified',
                'description' => 'Shop description modified',
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
