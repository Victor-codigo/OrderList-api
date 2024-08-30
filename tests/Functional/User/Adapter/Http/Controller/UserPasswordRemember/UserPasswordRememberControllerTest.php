<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserPasswordRemember;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class UserPasswordRememberControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const string ENDPOINT = '/api/v1/users/remember';
    private const string METHOD = 'POST';
    private const string EMAIL_ALREADY_EXISTS = 'email.already.exists@host.com';
    private const string EMAIL_NOT_FOUND = 'email.not.found@host.com';

    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getNewClient();
    }

    #[Test]
    public function itShouldAcceptRequestAndSendAnEmailOfConfirmation(): void
    {
        $clientData = [
            'email' => self::EMAIL_ALREADY_EXISTS,
            'email_password_remember_url' => 'http://www.domain.com',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Request accepted', $responseContent->message);

        $this->assertEmailIsSent(self::EMAIL_ALREADY_EXISTS);
    }

    #[Test]
    public function itShouldFailEmailIsWrong(): void
    {
        $clientData = [
            'email' => 'this is not an email',
            'email_password_remember_url' => 'http://www.domain.com',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['email'], $responseContent->errors->email);
        $this->assertSame('Invalid parameters', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    #[Test]
    public function itShouldFailEmailIsEmpty(): void
    {
        $clientData = [
            'email' => '',
            'email_password_remember_url' => 'http://www.domain.com',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['not_blank'], $responseContent->errors->email);
        $this->assertSame('Invalid parameters', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    #[Test]
    public function itShouldFailEmailIsNotSent(): void
    {
        $clientData = [
            'email_password_remember_url' => 'http://www.domain.com',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->email);
        $this->assertSame('Invalid parameters', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    #[Test]
    public function itShouldFailEmailNotFound(): void
    {
        $clientData = [
            'email' => self::EMAIL_NOT_FOUND,
            'email_password_remember_url' => 'http://www.domain.com',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['email_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Email not found', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    #[Test]
    public function itShouldFailPasswordRememberUrlIsMissed(): void
    {
        $clientData = [
            'email' => self::EMAIL_ALREADY_EXISTS,
            'email_password_remember_url' => null,
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['passwordRememberUrl'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->passwordRememberUrl);
        $this->assertSame('Invalid parameters', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    #[Test]
    public function itShouldFailPasswordRememberUrlIsBlank(): void
    {
        $clientData = [
            'email' => self::EMAIL_ALREADY_EXISTS,
            'email_password_remember_url' => '',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['passwordRememberUrl'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['not_blank'], $responseContent->errors->passwordRememberUrl);
        $this->assertSame('Invalid parameters', $responseContent->message);
        $this->assertEmailIsNotSent();
    }

    #[Test]
    public function itShouldFailPasswordRememberUrlIsWrong(): void
    {
        $clientData = [
            'email' => self::EMAIL_ALREADY_EXISTS,
            'email_password_remember_url' => 'www.domain.com',
        ];

        $this->client->request(method: self::METHOD, uri: self::ENDPOINT, content: json_encode($clientData));
        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['passwordRememberUrl'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame(['url'], $responseContent->errors->passwordRememberUrl);
        $this->assertSame('Invalid parameters', $responseContent->message);
        $this->assertEmailIsNotSent();
    }
}
