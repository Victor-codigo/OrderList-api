<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupCreate;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupCreateTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups';
    private const METHOD = 'POST';
    private const USER_NAME = 'email.already.active@host.com';
    private const USER_PASSWORD = '123456';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldCreateAGroup(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => str_pad('', 500, 'd'),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldCreateAGroupDescriptionIsNull(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupNameOtro',
                'description' => null,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group created', $responseContent->message);
        $this->assertIsString($responseContent->data->id);
    }

    /** @test */
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupName',
                'description' => str_pad('', 501, 'd'),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['description'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['string_too_long'], $responseContent->errors->description);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => null,
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->name);
    }

    /** @test */
    public function itShouldFailNameIsTooShort(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => '',
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['not_blank', 'string_too_short'], $responseContent->errors->name);
    }

    /** @test */
    public function itShouldFailNameIsTooLong(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => str_pad('', 51, 'h'),
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertEquals(['string_too_long'], $responseContent->errors->name);
    }

    /** @test */
    public function itShouldFailGroupNameAlreadyExists(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => 'GroupOne',
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_name_repeated'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('The group name already exists', $responseContent->message);
    }

    /** @test */
    public function itShouldFailNotPermission(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, 'not allowed user');
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'name' => str_pad('', 51, 'h'),
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
