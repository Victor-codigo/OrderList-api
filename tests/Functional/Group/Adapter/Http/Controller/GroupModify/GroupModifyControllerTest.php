<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupModify;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupModifyControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups/modify';
    private const METHOD = 'POST';
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    /** @test */
    public function itShouldModifyTheGroup(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'name' => 'GroupName',
                'description' => 'group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group modified', $responseContent->message);
        $this->assertSame(self::GROUP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldModifyTheGroupDescriptionIsNull(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'name' => 'GroupName',
                'description' => null,
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group modified', $responseContent->message);
        $this->assertSame(self::GROUP_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => null,
                'name' => 'GroupName',
                'description' => 'group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailGroupIdIsNotValid(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => 'not valid id',
                'name' => 'GroupName',
                'description' => 'group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    /** @test */
    public function itShouldFailNameIsNull(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'name' => null,
                'description' => 'group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['not_blank', 'not_null'], $responseContent->errors->name);
    }

    /** @test */
    public function itShouldFailNameIsNotValid(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'name' => 'not valid name+',
                'description' => 'group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['alphanumeric'], $responseContent->errors->name);
    }

    /** @test */
    public function itShouldFailDescriptionIsTooLong(): void
    {
        $this->client = $this->getNewClientAuthenticatedUser();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'name' => 'GroupName',
                'description' => str_pad('', 501, 'u'),
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['description'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);
        $this->assertSame(['string_too_long'], $responseContent->errors->description);
    }

    /** @test */
    public function itShouldFailUserSessionIsNotAdminInTheGroup(): void
    {
        $this->client = $this->getNewClientAuthenticatedAdmin();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => self::GROUP_ID,
                'name' => 'GroupName',
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Not permissions in this group', $responseContent->message);
        $this->assertSame('Not permissions in this group', $responseContent->errors->permissions);
    }

    /** @test */
    public function itShouldFailGroupNotFound(): void
    {
        $this->client = $this->getNewClientAuthenticatedAdmin();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'group_id' => '1befdbe2-9c14-42f0-850f-63e061e33b8f',
                'name' => 'GroupName',
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not found', $responseContent->message);
        $this->assertSame('Group not found', $responseContent->errors->group_not_found);
    }
}
