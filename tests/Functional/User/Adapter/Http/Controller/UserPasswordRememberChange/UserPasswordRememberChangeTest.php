<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserPasswordRememberChange;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Response\RESPONSE_STATUS;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\User;

class UserPasswordRememberChangeTest extends WebClientTestCase
{
    private const ENDPOINT = '/api/v1/en/user/password/remember/change';
    private const METHOD = 'PATCH';
    private const USER_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const USER_ID_NOT_EXISTS = '1befdbe2-9c14-42f0-850f-63e061e33b8k';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itshouldChangeThePassword(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID, 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, responseCode: Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Password changed', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::USER_ID)]);
        /** @var UserSymfonyAdapter $userPasswordHasher */
        $userPasswordHasher = $this->getContainer()->get(UserSymfonyAdapter::class);
        $userPasswordHasher->passwordHash($clientData['passwordNew']);

        $this->assertNotNull($user);
        $this->assertSame($userPasswordHasher->getPassword(), $user->getPassword()->getValue());
    }

    /** @test */
    public function itshouldFailTokenNotAValidToken(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID).'-wrong',
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong token', $responseContent->message);
    }

    /** @test */
    public function itshouldFailTokenWrong(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID).'-wrong',
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong token', $responseContent->message);
    }

    /** @test */
    public function itshouldFailTokenHasExpired(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID, 0),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Token has expired', $responseContent->message);
    }

    /** @test */
    public function itshouldFailPasswordNewIsTooShort(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID, 86_400),
            'passwordNew' => '12345',
            'passwordNewRepeat' => '123456',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNew'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNew' => ['string_too_short']], (array) $responseContent->errors);
    }

    /** @test */
    public function itshouldFailPasswordNewIsTooLong(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID, 86_400),
            'passwordNew' => str_pad('', 51, '-'),
            'passwordNewRepeat' => '123456',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNew'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNew' => ['string_too_long']], (array) $responseContent->errors);
    }

    /** @test */
    public function itshouldFailPasswordNewRepeatIsTooShort(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID, 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '12345',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNewRepeat'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNewRepeat' => ['string_too_short']], (array) $responseContent->errors);
    }

    /** @test */
    public function itshouldFailPasswordNewRepeatIsTooLong(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID, 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => str_pad('', 51, '-'),
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNewRepeat'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNewRepeat' => ['string_too_long']], (array) $responseContent->errors);
    }

    /** @test */
    public function itshouldFailIdDoesNotExists(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID_NOT_EXISTS, 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('It could not change password', $responseContent->message);
    }

    /** @test */
    public function itshouldFailIdPasswordNewAndPasswordNewRepeatAreNotEquals(): void
    {
        $this->client = $this->getNewClient();
        $clientData = [
            'token' => $this->generateToken(self::USER_ID, 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '1234567',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Password new and Repeat new are not equals', $responseContent->message);
    }
}
