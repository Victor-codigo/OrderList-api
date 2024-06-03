<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserModify;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Application\UserModify\BuiltInFunctionsReturn;
use User\Domain\Model\User;

require_once 'tests/BuiltinFunctions/UserApplicationUserModify.php';

class UserModifyControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const METHOD_FORM = 'POST';
    private const METHOD = 'PUT';
    private const ENDPOINT = '/api/v1/users/modify';
    private const USER_NAME = 'email.already.active@host.com';
    private const PATH_FIXTURES = __DIR__.'/Fixtures';
    private const PATH_IMAGE_UPLOAD = __DIR__.'/Fixtures/Image.png';
    private const PATH_IMAGE_BACKUP = 'tests/Fixtures/Files/Image.png';
    private const PATH_IMAGE_NOT_ALLOWED = __DIR__.'/Fixtures/MimeTypeNotAllowed.txt';
    private const PATH_IMAGE_NOT_ALLOWED_BACKUP = 'tests/Fixtures/Files/MimeTypeNotAllowed.txt';

    private readonly string $pathImageUser;
    private KernelBrowser|null $client;

    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(self::PATH_FIXTURES)) {
            mkdir(self::PATH_FIXTURES);
        }

        copy(self::PATH_IMAGE_BACKUP, self::PATH_IMAGE_UPLOAD);
        copy(self::PATH_IMAGE_NOT_ALLOWED_BACKUP, self::PATH_IMAGE_NOT_ALLOWED);

        $this->client = $this->getNewClientAuthenticatedUser();
        $this->pathImageUser = static::getContainer()->getParameter('user.image.path');

        if (!file_exists($this->pathImageUser)) {
            mkdir($this->pathImageUser, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$is_readable = null;
        BuiltInFunctionsReturn::$unlink = null;
        BuiltInFunctionsReturn::$file_exists = null;

        $this->removeFolderFiles($this->pathImageUser);
        rmdir($this->pathImageUser);
    }

    /** @test */
    public function itShouldModifyTheUserNameAndImage(): void
    {
        $name = 'MariaMod';
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User modified', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => ValueObjectFactory::createEmail(self::USER_NAME)]);

        $this->assertEquals($name, $user->getName()->getValue());
        $this->assertNotNull($user->getProfile()->getImage()->getValue());
    }

    /** @test */
    public function itShouldModifyTheUserImageIsNull(): void
    {
        $name = 'MariaMod';
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User modified', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => ValueObjectFactory::createEmail(self::USER_NAME)]);

        $this->assertEquals($name, $user->getName()->getValue());
    }

    /** @test */
    public function itShouldModifyTheUserImageRemoveIsNull(): void
    {
        $name = 'MariaMod';
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User modified', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => ValueObjectFactory::createEmail(self::USER_NAME)]);

        $this->assertEquals($name, $user->getName()->getValue());
        $this->assertNotNull($user->getProfile()->getImage()->getValue());
    }

    /** @test */
    public function itShouldModifyTheUserImageRemoveIsTrue(): void
    {
        $name = 'MariaMod';
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => true,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User modified', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => ValueObjectFactory::createEmail(self::USER_NAME)]);

        $this->assertEquals($name, $user->getName()->getValue());
        $this->assertNull($user->getProfile()->getImage()->getValue());
    }

    /** @test */
    public function itShouldModifyTheUserImageRemoveIsTrueAndImageIsNotNull(): void
    {
        $name = 'MariaMod';
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => true,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User modified', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => ValueObjectFactory::createEmail(self::USER_NAME)]);

        $this->assertEquals($name, $user->getName()->getValue());
        $this->assertNotNull($user->getProfile()->getImage()->getValue());
    }

    /** @test */
    public function itShouldFailNameIsTooShort(): void
    {
        $name = '';
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailNameIsTooLarge(): void
    {
        $name = str_pad('', 51, 'u');
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImageMimeTypeNotAllowed(): void
    {
        $name = 'maria';
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_NOT_ALLOWED, 'MimeTypeNotAllowed.txt', 'text/plain', UPLOAD_ERR_OK, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailCanNotDeleteUserFormerImage(): void
    {
        $this->getEntityManager()->getConnection()->update(
            'Profile',
            ['image' => 'formerImage'],
            ['id' => '2606508b-4516-45d6-93a6-c7cb416b7f3f']
        );

        $name = 'MariaMod';
        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_OK, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['internal'], Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('An error has been occurred', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImageSizeFormTooLarge(): void
    {
        $name = 'MariaMod';
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_FORM_SIZE, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImageSizeIniTooLarge(): void
    {
        $name = 'MariaMod';
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_INI_SIZE, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImageNoUploaded(): void
    {
        $name = 'MariaMod';
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_FILE, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImagePartiallyUploaded(): void
    {
        $name = 'MariaMod';
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_PARTIAL, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImageCantWrite(): void
    {
        $name = 'MariaMod';
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_CANT_WRITE, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImageErrorExtension(): void
    {
        $name = 'MariaMod';
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_EXTENSION, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailImageErrorTmpDir(): void
    {
        $name = 'MariaMod';
        BuiltInFunctionsReturn::$unlink = false;
        $this->client->request(
            method: self::METHOD_FORM,
            uri: self::ENDPOINT,
            parameters: [
                '_method' => self::METHOD,
            ],
            content: json_encode([
                'name' => $name,
                'image_remove' => false,
            ]),
            files: [
                'image' => new UploadedFile(self::PATH_IMAGE_UPLOAD, 'image.png', 'image/png', UPLOAD_ERR_NO_TMP_DIR, true),
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['image'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }
}
