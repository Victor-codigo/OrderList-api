<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupRemoveAllUserGroups;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupRemoveAllUserGroupsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/groups/user/remove-groups';
    private const string METHOD = 'DELETE';
    private const string SYSTEM_KEY = 'systemKeyForDev';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function userHasGroupsIsAdminGroupsIsUserAndChangeAdmins(): void
    {
        $groupsIdToRemove = [
            'e05b2466-9528-4815-ac7f-663c1d89ab55',
            'a5002966-dbf7-4f76-a862-23a04b5ca465',
        ];
        $groupsIdUserRemoved = [
            '78b96ac1-ffcc-458b-8f48-b40c6e65261f',
            'fdb242b4-bac8-4463-88d0-0941bb0beee0',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
        ];
        $groupsIdUserSetAsAdmin = [[
            'group_id' => 'fdb242b4-bac8-4463-88d0-0941bb0beee0',
            'user_id' => '1befdbe2-9c14-42f0-850f-63e061e33b8f',
        ], [
            'group_id' => '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'user_id' => '4d59c61e-4f51-3ffe-b4b9-e622436b6fa3',
        ]];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['groups_id_removed', 'groups_id_user_removed', 'groups_id_user_set_as_admin'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User removed from all groups', $responseContent->message);

        $this->assertEqualsCanonicalizing($groupsIdToRemove, $responseContent->data->groups_id_removed);
        $this->assertEqualsCanonicalizing($groupsIdUserRemoved, $responseContent->data->groups_id_user_removed);

        foreach ($responseContent->data->groups_id_user_set_as_admin as $key => $groupAndUser) {
            $this->assertEqualsCanonicalizing($groupsIdUserSetAsAdmin[$key]['group_id'], $groupAndUser->group_id);
            $this->assertIsString($groupAndUser->user_id);
        }
    }

    /** @test */
    public function userHasOnlyGroupsToRemove(): void
    {
        $groupsIdToRemove = [
            'b4519488-0861-4827-b238-f21442bf4cbe',
            '8bb9e2a0-2ded-429c-a0ee-599d46fa9ce7',
        ];

        $client = $this->getNewClientAuthenticated('email.other_6.active@host.com', '123456');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['groups_id_removed', 'groups_id_user_removed', 'groups_id_user_set_as_admin'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User removed from all groups', $responseContent->message);

        $this->assertEqualsCanonicalizing($groupsIdToRemove, $responseContent->data->groups_id_removed);
        $this->assertEmpty($responseContent->data->groups_id_user_removed);
        $this->assertEmpty($responseContent->data->groups_id_user_set_as_admin);
    }

    /** @test */
    public function userHasUserToRemove(): void
    {
        $groupsIdUserRemoved = [
            'fdb242b4-bac8-4463-88d0-0941bb0beee0',
        ];

        $client = $this->getNewClientAuthenticated('email.other_3.active@host.com', '123456');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => self::SYSTEM_KEY,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['groups_id_removed', 'groups_id_user_removed', 'groups_id_user_set_as_admin'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User removed from all groups', $responseContent->message);

        $this->assertEmpty($responseContent->data->groups_id_removed);
        $this->assertEqualsCanonicalizing($groupsIdUserRemoved, $responseContent->data->groups_id_user_removed);
        $this->assertEmpty($responseContent->data->groups_id_user_set_as_admin);
    }

    /** @test */
    public function itShouldFailSystemKeyIsNull(): void
    {
        $client = $this->getNewClientAuthenticated('email.other_3.active@host.com', '123456');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank'], $responseContent->errors->system_key);
    }

    /** @test */
    public function itShouldFailSystemKeyIsWrong(): void
    {
        $client = $this->getNewClientAuthenticated('email.other_3.active@host.com', '123456');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'system_key' => 'wrong system key',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['system_key'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('System key is wrong', $responseContent->message);

        $this->assertEquals('System key is wrong', $responseContent->errors->system_key);
    }
}
