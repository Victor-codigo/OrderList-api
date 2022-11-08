<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserRegister;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Domain\Model\Profile;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class UserRegisterControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const ENDPOINT = '/api/v1/users';
    private const METHOD = 'POST';

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldFailUserAlreadyExists(): void
    {
        $clientData = [
            'name' => 'Juanito',
            'email' => 'email.already.exists@host.com',
            'password' => 'qwerty',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client = $this->getNewClient();
        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('The email already exists', $content->message);
    }

    /** @test */
    public function itShouldCreateTheUserNameLowerBound(): void
    {
        $clientData = [
            'name' => 'Anas',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client = $this->getNewClient();
        $this->client->disableReboot();
        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);

        $this->assertSame('User created', $content->message);
        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertUserRegisterdIsOk(
            $content->data->id,
            $clientData['password'],
            $clientData['email'],
            $clientData['name'],
            [USER_ROLES::NOT_ACTIVE]
        );

        $this->assertEmailIsSent($clientData['email']);
    }

    /** @test */
    public function itShouldCreateTheUserNameUpperBound(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => str_pad('', 50, 's'),
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);

        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);
        $this->assertSame('User created', $content->message);

        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertEmpty($content->errors);

        $this->assertUserRegisterdIsOk(
            $content->data->id,
            $clientData['password'],
            $clientData['email'],
            $clientData['name'],
            [USER_ROLES::NOT_ACTIVE]
        );

        $this->assertEmailIsSent($clientData['email']);
    }

    /** @test */
    public function itShouldFailOnNameTooShort(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Ana',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['string_too_short'], $content->errors->name);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailOnNameTooLong(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => str_pad('', 51, 's'),
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['string_too_long'], $content->errors->name);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailOnNameNotAlphanumeric(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'sdfg*',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['alphanumeric'], $content->errors->name);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailNameNotSet(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'not_null'], $content->errors->name);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailOnEmail(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'Email wrong',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['email'], $content->errors->email);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailEmailNotSet(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'not_null'], $content->errors->email);

        $this->assertRowDoesntExistInDataBase('name', new Name($clientData['name']), User::class);
    }

    /** @test */
    public function itShoulCreateTheUserPasswordBoundLower(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);

        $this->assertSame('User created', $content->message);
        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertUserRegisterdIsOk(
            $content->data->id,
            $clientData['password'],
            $clientData['email'],
            $clientData['name'],
            [USER_ROLES::NOT_ACTIVE]
        );

        $this->assertEmailIsSent($clientData['email']);
    }

    /** @test */
    public function itShoulCreateTheUserPasswordBoundUpper(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => str_pad('', 50, '-'),
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);

        $this->assertSame('User created', $content->message);
        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertUserRegisterdIsOk(
            $content->data->id,
            $clientData['password'],
            $clientData['email'],
            $clientData['name'],
            [USER_ROLES::NOT_ACTIVE]
        );

        $this->assertEmailIsSent($clientData['email']);
    }

    /** @test */
    public function itShoulFailPasswordTooshort(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => '12345',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);
        $this->assertSame(['string_too_short'], $content->errors->password);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShoulFailPasswordTooLong(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => str_pad('', 51, '-'),
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);
        $this->assertSame(['string_too_long'], $content->errors->password);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShoulFailPasswordNotSet(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'not_null'], $content->errors->password);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShoulFailKeyNotValid(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'key' => 'not_valid_key',
        ];

        $this->client->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Invalid registration key', $content->message);

        $this->assertRowDoesntExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    private function assertUserRegisterdIsOk(string $userId, string $password, string $email, string $name, array $roles): void
    {
        $entityManager = $this->getEntityManager();
        $userRepository = $entityManager->getRepository(User::class);
        $userIdentifier = new Identifier($userId);
        $userSaved = $userRepository->findOneBy(['id' => $userIdentifier]);

        $this->assertSame($name, $userSaved->getName()->getValue());
        $this->assertSame($email, $userSaved->getEmail()->getValue());
        $this->assertEquals((new \DateTime())->format('Y-m-d H:m'), $userSaved->getCreatedOn()->format('Y-m-d H:m'));
        $this->assertNotEmpty($password);

        $rolesSaved = array_map(fn (Rol $rol) => $rol->getValue(), $userSaved->getRoles()->getValue());

        foreach ($roles as $rol) {
            $this->assertContains($rol, $rolesSaved);
        }

        $profileRepository = $entityManager->getRepository(Profile::class);
        $profileSaved = $profileRepository->findOneBy(['id' => $userIdentifier]);

        $this->assertNull($profileSaved->getImage());
    }
}
