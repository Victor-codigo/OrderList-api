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

    private const ENDPOINT = '/api/v1/groups/user/remove-groups';
    private const METHOD = 'DELETE';

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
            'user_id' => '0b13e52d-b058-32fb-8507-10dec634a07c',
        ]];

        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['groups_id_removed', 'groups_id_user_removed', 'groups_id_user_set_as_admin'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User removed from all groups', $responseContent->message);

        $this->assertEqualsCanonicalizing($groupsIdToRemove, $responseContent->data->groups_id_removed);
        $this->assertEqualsCanonicalizing($groupsIdUserRemoved, $responseContent->data->groups_id_user_removed);

        foreach ($responseContent->data->groups_id_user_set_as_admin as $key => $groupAndUser) {
            $this->assertEqualsCanonicalizing($groupsIdUserSetAsAdmin[$key], (array) $groupAndUser);
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
            content: json_encode([])
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
            content: json_encode([])
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
}
