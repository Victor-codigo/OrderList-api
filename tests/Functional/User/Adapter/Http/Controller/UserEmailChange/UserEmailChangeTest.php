<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserEmailChange;

use Override;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Domain\Model\User;

class UserEmailChangeTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/users/email';
    private const string METHOD = 'PATCH';
    private const string USER_PASSWORD = '123456';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldModifyTheEmail(): void
    {
        $emailNew = 'new.email@host.com';
        $password = self::USER_PASSWORD;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'email' => $emailNew,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Email modified', $responseContent->message);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => ValueObjectFactory::createEmail($emailNew)]);

        $this->assertEquals($emailNew, $user->getEmail()->getValue());
    }

    /** @test */
    public function itShouldFailEmailIsNull(): void
    {
        $emailNew = null;
        $password = self::USER_PASSWORD;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'email' => $emailNew,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailEmailNotValid(): void
    {
        $emailNew = 'new.email@host';
        $password = self::USER_PASSWORD;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'email' => $emailNew,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailPasswordIsNull(): void
    {
        $emailNew = 'new.email@host.com';
        $password = null;

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'email' => $emailNew,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailPasswordIsTooShort(): void
    {
        $emailNew = 'new.email@host.com';
        $password = '12345';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'email' => $emailNew,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailPasswordIsTooLong(): void
    {
        $emailNew = 'new.email@host.com';
        $password = str_pad('', 51, 'p');

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'email' => $emailNew,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
    }

    /** @test */
    public function itShouldFailPasswordIsWrong(): void
    {
        $emailNew = 'new.email@host.com';
        $password = 'wrong password';

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'email' => $emailNew,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['password_wrong'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Password is wrong', $responseContent->message);
    }
}
