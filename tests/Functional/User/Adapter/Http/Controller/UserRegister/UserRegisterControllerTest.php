<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserRegister;

use Override;
use DateTime;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\User\USER_ROLES;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Domain\Model\Profile;
use User\Domain\Model\User;

class UserRegisterControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const string ENDPOINT = '/api/v1/users';
    private const string METHOD = 'POST';

    #[Override]
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
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated = $this->getNewClient();
        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email_exists' => 'email_exists'], Response::HTTP_BAD_REQUEST);

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
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated = $this->getNewClient();
        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);

        $this->assertSame('User created', $content->message);
        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertUserRegisteredIsOk(
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
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => str_pad('', 50, 's'),
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);

        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);
        $this->assertSame('User created', $content->message);

        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertEmpty($content->errors);

        $this->assertUserRegisteredIsOk(
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
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => '',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'string_too_short'], $content->errors->name);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailOnNameTooLong(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => str_pad('', 51, 's'),
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['string_too_long'], $content->errors->name);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailOnNameNotAlphanumeric(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'sdfg*',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['alphanumeric_with_whitespace'], $content->errors->name);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailNameNotSet(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'not_null'], $content->errors->name);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailOnEmail(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'Email wrong',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['email'], $content->errors->email);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailEmailNotSet(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);

        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);
        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'not_null'], $content->errors->email);

        $this->assertRowDoesNotExistInDataBase('name', new NameWithSpaces($clientData['name']), User::class);
    }

    /** @test */
    public function itShouldCreateTheUserPasswordBoundLower(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);

        $this->assertSame('User created', $content->message);
        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertUserRegisteredIsOk(
            $content->data->id,
            $clientData['password'],
            $clientData['email'],
            $clientData['name'],
            [USER_ROLES::NOT_ACTIVE]
        );

        $this->assertEmailIsSent($clientData['email']);
    }

    /** @test */
    public function itShouldCreateTheUserPasswordBoundUpper(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => str_pad('', 50, '-'),
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertSame(RESPONSE_STATUS::OK->value, $content->status);

        $this->assertSame('User created', $content->message);
        $this->assertIsString($content->data->id);
        $this->assertEquals(36, strlen($content->data->id));

        $this->assertUserRegisteredIsOk(
            $content->data->id,
            $clientData['password'],
            $clientData['email'],
            $clientData['name'],
            [USER_ROLES::NOT_ACTIVE]
        );

        $this->assertEmailIsSent($clientData['email']);
    }

    /** @test */
    public function itShouldFailPasswordToShort(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => '12345',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);
        $this->assertSame(['string_too_short'], $content->errors->password);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailPasswordTooLong(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => str_pad('', 51, '-'),
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);
        $this->assertSame(['string_too_long'], $content->errors->password);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailPasswordNotSet(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'key' => '23db9ca1-1568-473e-8c23-c4613205cf36',
            'email_confirmation_url' => 'http://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'not_null'], $content->errors->password);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShouldFailUrlConfirmEmailNotSet(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => '123456',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email_confirmation_url'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);
        $this->assertSame(['not_blank', 'not_null'], $content->errors->email_confirmation_url);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    /** @test */
    public function itShoulFailUrlConfirmEmailNotValid(): void
    {
        static::$clientNoAuthenticated = $this->getNewClient();
        $clientData = [
            'name' => 'Anastasia',
            'email' => 'anastasia@host.com',
            'password' => '123456',
            'email_confirmation_url' => 'htt://www.domain.com/users/confirm',
        ];

        static::$clientNoAuthenticated->request(
            self::METHOD,
            self::ENDPOINT,
            [],
            [],
            [],
            json_encode($clientData)
        );

        $response = static::$clientNoAuthenticated->getResponse();
        $content = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email_confirmation_url'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $content->status);

        $this->assertSame('Error', $content->message);

        $this->assertRowDoesNotExistInDataBase('email', new Email($clientData['email']), User::class);
    }

    private function assertUserRegisteredIsOk(string $userId, string $password, string $email, string $name, array $roles): void
    {
        $entityManager = $this->getEntityManager();
        $userRepository = $entityManager->getRepository(User::class);
        $userIdentifier = new Identifier($userId);
        $userSaved = $userRepository->findOneBy(['id' => $userIdentifier]);

        $this->assertSame($name, $userSaved->getName()->getValue());
        $this->assertSame($email, $userSaved->getEmail()->getValue());
        $this->assertEquals((new DateTime())->format('Y-m-d H:m'), $userSaved->getCreatedOn()->format('Y-m-d H:m'));
        $this->assertNotEmpty($password);

        $rolesSaved = array_map(fn (Rol $rol) => $rol->getValue(), $userSaved->getRoles()->getValue());

        foreach ($roles as $rol) {
            $this->assertContains($rol, $rolesSaved);
        }

        $profileRepository = $entityManager->getRepository(Profile::class);
        $profileSaved = $profileRepository->findOneBy(['id' => $userIdentifier]);

        $this->assertNull($profileSaved->getImage()->getValue());
    }
}
