<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupRemove;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupRemoveControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups';
    private const METHOD = 'DELETE';
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldRemoveTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group removed', $responseContent->message);
        $this->assertSame(self::GROUP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'not valid id',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGroupIdNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => '467fef4a-d3a9-4d08-9430-c06da70fd231',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not found', $responseContent->message);
        $this->assertSame('Group not found', $responseContent->errors->group_not_found);
    }

    /** @test */
    public function itShouldFailUserIdIsNotAdmin(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Not permissions in this group', $responseContent->message);
        $this->assertSame('Not permissions in this group', $responseContent->errors->permissions);
    }
}
