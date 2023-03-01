<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserPasswordChange;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\User;

class UserPasswordChangeControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/users/password';
    private const METHOD = 'PATCH';
    private const ID_USER = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const ID_USER_NOT_ACTIVE = 'bd2cbad1-6ccf-48e3-bb92-bc9961bc011e';
    private const USER_PASSWORD = '123456';

    /** @test */
    public function itShouldChangeTheUserPassword(): void
    {
        $clientData = [
            'id' => self::ID_USER,
            'passwordOld' => self::USER_PASSWORD,
            'passwordNew' => 'newPassword',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, responseCode: Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Password changed', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $userSaved = $userRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::ID_USER)]);
        /** @var UserSymfonyAdapter $userHasher */
        $userHasher = $client->getContainer()->get(UserSymfonyAdapter::class);

        $this->assertEquals($userHasher->getPassword(), $userSaved->getpassword()->getValue());
    }

    /** @test */
    public function itShouldFailDataNotSent(): void
    {
        $clientData = [];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['id', 'password_old', 'password_new', 'password_new_repeat']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);
        $errorExpected = new \stdClass();
        $errorExpected->id = ['not_blank', 'not_null'];
        $errorExpected->password_old = ['not_blank', 'not_null'];
        $errorExpected->password_new = ['not_blank', 'not_null'];
        $errorExpected->password_new_repeat = ['not_blank', 'not_null'];
        $this->assertEquals($errorExpected, $responseContent->errors);
    }

    /** @test */
    public function itShouldFailIdNotValid(): void
    {
        $clientData = [
            'id' => 'this is not an id',
            'passwordOld' => '123456',
            'passwordNew' => 'newPassword',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['id']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);
        $errorExpected = new \stdClass();
        $errorExpected->id = ['uuid_invalid_characters'];
        $this->assertEquals($errorExpected, $responseContent->errors);
    }

    /** @test */
    public function itShouldFailPasswordOldNotValid(): void
    {
        $clientData = [
            'id' => self::ID_USER,
            'passwordOld' => '12345',
            'passwordNew' => 'newPassword',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['password_old']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);
        $errorExpected = new \stdClass();
        $errorExpected->password_old = ['string_too_short'];
        $this->assertEquals($errorExpected, $responseContent->errors);
    }

    /** @test */
    public function itShouldFailPasswordNewNotValid(): void
    {
        $clientData = [
            'id' => self::ID_USER,
            'passwordOld' => '123456',
            'passwordNew' => 'newp',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['password_new']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);
        $errorExpected = new \stdClass();
        $errorExpected->password_new = ['string_too_short'];
        $this->assertEquals($errorExpected, $responseContent->errors);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatNotValid(): void
    {
        $clientData = [
            'id' => self::ID_USER,
            'passwordOld' => '123456',
            'passwordNew' => 'passwordNew',
            'passwordNewRepeat' => 'newpa',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['password_new_repeat']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);
        $errorExpected = new \stdClass();
        $errorExpected->password_new_repeat = ['string_too_short'];
        $this->assertEquals($errorExpected, $responseContent->errors);
    }

    /** @test */
    public function itShouldFailIdUserDoesNotExists(): void
    {
        $clientData = [
            'id' => '2606508b-4516-45d6-93a6-c7cb416b7f32',
            'passwordOld' => '123456',
            'passwordNew' => 'passwordNew',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['password_change']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('It could not change password', $responseContent->message);
    }

    /** @test */
    public function itShouldFailIdUserPasswordIsWrong(): void
    {
        $clientData = [
            'id' => self::ID_USER,
            'passwordOld' => '123456-',
            'passwordNew' => 'passwordNew',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['password_new']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Password old is wrong', $responseContent->message);
    }

    /** @test */
    public function itShouldFailIdUserIsNotActive(): void
    {
        $clientData = [
            'id' => self::ID_USER_NOT_ACTIVE,
            'passwordOld' => 'qwerty',
            'passwordNew' => 'passwordNew',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['permissions']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('User is not active', $responseContent->message);
    }

    /** @test */
    public function itShouldFailIdUserPasswordNewAndRepeatNewAreNotEquals(): void
    {
        $clientData = [
            'id' => self::ID_USER,
            'passwordOld' => '123456',
            'passwordNew' => 'passwordNew',
            'passwordNewRepeat' => 'newPassword',
        ];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(
            response: $response,
            responseCode: Response::HTTP_BAD_REQUEST,
            errors: ['password_new_repeat']
        );
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Password new and Repeat new are not equals', $responseContent->message);
    }
}
