<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserRemove;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Adapter\Http\Controller\UserRemove\UserRemoveController;

class UserRemoveControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const METHOD = 'DELETE';
    private const ENDPOINT = '/api/v1/users/remove/';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const USER_OTHER_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const USER_ID_NOT_EXISTS = 'f876ffe5-df2e-4597-aea5-b236e0663c95';

    private UserRemoveController $object;

    /** @test */
    public function itShouldRemoveTheUserSameUserPermissions(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::USER_ID,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User Removed', $responseContent->message);
    }

    /** @test */
    public function itShouldRemoveTheUserAdminPermissions(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::USER_ID,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User Removed', $responseContent->message);
    }

    /** @test */
    public function itShouldFailUserIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::USER_ID.'-wrong',
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['id'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Invalid id', $responseContent->message);
    }

    /** @test */
    public function itShouldFailUserHasNotPermissions(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::USER_OTHER_ID,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have no permissions', $responseContent->message);
    }

    /** @test */
    public function itShouldFailUserNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::USER_ID_NOT_EXISTS,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['user_not_found'], Response::HTTP_NOT_FOUND);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('User not found', $responseContent->message);
    }
}
