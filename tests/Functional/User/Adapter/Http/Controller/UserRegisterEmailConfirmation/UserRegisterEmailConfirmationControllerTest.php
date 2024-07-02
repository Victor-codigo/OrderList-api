<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserRegisterEmailConfirmation;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Validation\User\USER_ROLES;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Domain\Model\User;

class UserRegisterEmailConfirmationControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/users/confirm';
    private const string METHOD = 'PATCH';
    private const string USER_ID = 'bd2cbad1-6ccf-48e3-bb92-bc9961bc011e';
    private const string USER_ID_ALREADY_REGISTERED = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string USER_ID_NOT_EXISTS = 'NO-EXISTS-4516-45d6-93a6-c7cb416b7f3f';

    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getNewClientNoAuthenticated();
    }

    /** @test */
    public function itShouldActivateTheUser(): void
    {
        $token = $this->generateToken(['username' => self::USER_ID], 86_400); // 24H
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode(['token' => $token])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['username'], [], Response::HTTP_OK);
        $this->assertSame('User activated', $responseContent->message);
        $this->assertSame(self::USER_ID, $responseContent->data->username);

        $entityManager = $this->getEntityManager();
        $userSaved = $entityManager->find(User::class, self::USER_ID);

        $this->assertSame(self::USER_ID, $userSaved->getId()->getValue());
        $this->assertContainsEquals(new Rol(USER_ROLES::USER_FIRST_LOGIN), $userSaved->getRoles()->getValue());
    }

    /** @test */
    public function itShouldFailWrongToken(): void
    {
        $token = $this->generateToken(['username' => self::USER_ID], 86_400); // 24H
        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode(['token' => $token.'-Wrong token']));

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['token_error'], Response::HTTP_BAD_REQUEST);
        $this->assertSame('Wrong token', $responseContent->message);

        $entityManager = $this->getEntityManager();
        $userSaved = $entityManager->find(User::class, new Identifier(self::USER_ID));

        $this->assertEquals([new Rol(USER_ROLES::NOT_ACTIVE)], $userSaved->getRoles()->getValue());
    }

    /** @test */
    public function itShouldFailTokenHasExpired(): void
    {
        $token = $this->generateToken(['username' => self::USER_ID], 0);
        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode(['token' => $token]));

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['token_expired'], Response::HTTP_BAD_REQUEST);
        $this->assertSame('Token has expired', $responseContent->message);

        $entityManager = $this->getEntityManager();
        $userSaved = $entityManager->find(User::class, new Identifier(self::USER_ID));

        $this->assertEquals([new Rol(USER_ROLES::NOT_ACTIVE)], $userSaved->getRoles()->getValue());
    }

    /** @test */
    public function itShouldFailUserIsAlreadyActive(): void
    {
        $token = $this->generateToken(['username' => self::USER_ID_ALREADY_REGISTERED], 86_400); // 24H
        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode(['token' => $token]));

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email_verified'], Response::HTTP_BAD_REQUEST);
        $this->assertSame('User already active', $responseContent->message);

        $entityManager = $this->getEntityManager();
        $userSaved = $entityManager->find(User::class, new Identifier(self::USER_ID_ALREADY_REGISTERED));

        $this->assertEquals([new Rol(USER_ROLES::USER)], $userSaved->getRoles()->getValue());
    }

    /** @test */
    public function itShouldFailUserDoesNotExists(): void
    {
        $token = $this->generateToken(['username' => self::USER_ID_NOT_EXISTS], 86_400); // 24H
        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode(['token' => $token]));

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['token_error'], Response::HTTP_BAD_REQUEST);
        $this->assertSame('Wrong token', $responseContent->message);

        $entityManager = $this->getEntityManager();
        $userSaved = $entityManager->find(User::class, new Identifier(self::USER_ID_NOT_EXISTS));

        $this->assertNull($userSaved);
    }

    /** @test */
    public function itShouldFailTokenValidation(): void
    {
        $token = 'is.not.valid-token';
        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode(['token' => $token]));

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['token'], Response::HTTP_BAD_REQUEST);
        $this->assertSame('Wrong token', $responseContent->message);

        $this->assertSame(['string_too_short'], $responseContent->errors->token);

        $entityManager = $this->getEntityManager();
        $userSaved = $entityManager->find(User::class, new Identifier(self::USER_ID_NOT_EXISTS));

        $this->assertNull($userSaved);
    }
}
