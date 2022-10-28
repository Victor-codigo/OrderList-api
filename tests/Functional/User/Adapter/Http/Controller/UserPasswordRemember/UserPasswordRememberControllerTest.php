<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserPasswordRemember;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class UserPasswordRememberControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const ENDPOINT = '/api/v1/en/user/password/remember';
    private const METHOD = 'PATCH';
    private const EMAIL_ALREADY_EXISTS = 'email.already.exists@host.com';
    private const EMAIL_NOT_FOUND = 'email.not.found@host.com';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldAcceptRequestAndSendAnEmailOfConfirmation(): void
    {
        $clientData = [
            'email' => self::EMAIL_ALREADY_EXISTS,
        ];

        $client = $this->getNewClient();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Request acepted', $responseContent->message);

        $this->assertEmailIsSent(self::EMAIL_ALREADY_EXISTS);
    }

    /** @test */
    public function itShouldFailEmailIsWrong(): void
    {
        $clientData = [
            'email' => 'this is not an email',
        ];

        $client = $this->getNewClient();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['email'], $responseContent->errors->email);
        $this->assertSame('Invalid email', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    /** @test */
    public function itShouldFailEmailIsEmpty(): void
    {
        $clientData = [
            'email' => '',
        ];

        $client = $this->getNewClient();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['not_blank'], $responseContent->errors->email);
        $this->assertSame('Invalid email', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    /** @test */
    public function itShouldFailEmailIsNotSent(): void
    {
        $clientData = [
        ];

        $client = $this->getNewClient();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->email);
        $this->assertSame('Invalid email', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    /** @test */
    public function itShouldFailEmailNotFound(): void
    {
        $clientData = [
            'email' => self::EMAIL_NOT_FOUND,
        ];

        $client = $this->getNewClient();
        $client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Email not found', $responseContent->message);
        $this->assertEmailIsNotSent();
    }
}
