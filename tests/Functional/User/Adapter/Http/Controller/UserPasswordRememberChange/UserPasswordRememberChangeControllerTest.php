<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserPasswordRememberChange;

use Override;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Domain\Model\User;

class UserPasswordRememberChangeControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const string ENDPOINT = '/api/v1/users/password-remember';
    private const string METHOD = 'PATCH';
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string USER_ID_NOT_EXISTS = '1befdbe2-9c14-42f0-850f-63e061e33b8k';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldChangeThePassword(): void
    {
        $client = $this->getNewClientAuthenticatedUser();

        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID], 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
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
    public function itShouldFailTokenNotAValidToken(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID]).'-wrong',
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['token_wrong'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong token jwt', $responseContent->message);
    }

    /** @test */
    public function itShouldFailTokenWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID]).'-wrong',
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['token_wrong'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong token jwt', $responseContent->message);
    }

    /** @test */
    public function itShouldFailTokenHasExpired(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID], 0),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['token_expired'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Token has expired', $responseContent->message);
    }

    /** @test */
    public function itShouldFailPasswordNewIsTooShort(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID], 86_400),
            'passwordNew' => '12345',
            'passwordNewRepeat' => '123456',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNew'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNew' => ['string_too_short']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailPasswordNewIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID], 86_400),
            'passwordNew' => str_pad('', 51, '-'),
            'passwordNewRepeat' => '123456',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNew'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNew' => ['string_too_long']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatIsTooShort(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID], 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '12345',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNewRepeat'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNewRepeat' => ['string_too_short']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailPasswordNewRepeatIsTooLong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID], 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => str_pad('', 51, '-'),
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['passwordNewRepeat'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Wrong password', $responseContent->message);

        $this->assertSame(['passwordNewRepeat' => ['string_too_long']], (array) $responseContent->errors);
    }

    /** @test */
    public function itShouldFailIdDoesNotExists(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID_NOT_EXISTS], 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '123456',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['password_change'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('It could not change password', $responseContent->message);
    }

    /** @test */
    public function itShouldFailIdPasswordNewAndPasswordNewRepeatAreNotEquals(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $clientData = [
            'token' => $this->generateToken(['username' => self::USER_ID], 86_400),
            'passwordNew' => '123456',
            'passwordNewRepeat' => '1234567',
        ];

        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk(response: $response, errors: ['password_repeat'], responseCode: Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Password new and Repeat new are not equals', $responseContent->message);
    }
}
