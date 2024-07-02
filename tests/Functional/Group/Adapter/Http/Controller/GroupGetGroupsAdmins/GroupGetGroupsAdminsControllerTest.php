<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupGetGroupsAdmins;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupGetGroupsAdminsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/groups/all/admins/';
    private const string METHOD = 'GET';
    private const array GROUP_ID = [
        'fdb242b4-bac8-4463-88d0-0941bb0beee0',
        '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
        '78b96ac1-ffcc-458b-8f48-b40c6e65261f',
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldGetAdminsOfGroups(): void
    {
        $page = 1;
        $pageItems = 100;
        $groupsAdminsExpected = [
            self::GROUP_ID[0] => [
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            ],
            self::GROUP_ID[1] => [
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            ],
            self::GROUP_ID[2] => [
                'b11c9be1-b619-4ef5-be1b-a1cd9ef265b7',
            ],
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .implode(',', self::GROUP_ID)
                ."?page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Admins of the groups', $responseContent->message);

        $this->assertObjectHasProperty('page', $responseContent->data);
        $this->assertObjectHasProperty('pages_total', $responseContent->data);
        $this->assertObjectHasProperty('groups', $responseContent->data);

        $this->assertEquals($page, $responseContent->data->page);
        $this->assertEquals(1, $responseContent->data->pages_total);
        $this->assertEquals($groupsAdminsExpected, (array) $responseContent->data->groups);
    }

    /** @test */
    public function itShouldGetAdminsOf100Groups(): void
    {
        $page = 1;
        $pageItems = 100;
        $groupsId = array_merge(
            array_fill(0, 100, self::GROUP_ID[0]),
            [self::GROUP_ID[1]]
        );
        $groupsAdminsExpected = [
            self::GROUP_ID[0] => [
                '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            ],
        ];
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .implode(',', $groupsId)
                ."?page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['page', 'pages_total', 'groups'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Admins of the groups', $responseContent->message);

        $this->assertObjectHasProperty('page', $responseContent->data);
        $this->assertObjectHasProperty('pages_total', $responseContent->data);
        $this->assertObjectHasProperty('groups', $responseContent->data);

        $this->assertEquals($page, $responseContent->data->page);
        $this->assertEquals(1, $responseContent->data->pages_total);
        $this->assertEquals($groupsAdminsExpected, (array) $responseContent->data->groups);
    }

    /** @test */
    public function itShouldFailGroupsIdIsWrong(): void
    {
        $page = 1;
        $pageItems = 100;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .implode(',', self::GROUP_ID).',wrong-id'
                ."?page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['groups_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals([['uuid_invalid_characters']], (array) $responseContent->errors->groups_id);
    }

    /** @test */
    public function itShouldFailGroupsIdIsNotFound(): void
    {
        $page = 1;
        $pageItems = 100;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .'6a0bc88a-7474-47f7-a443-d7bcffd4b825,ed7be8ff-715d-41e7-9c91-3ca36756b681'
                ."?page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $pageItems = 1;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .implode(',', self::GROUP_ID)
                ."?page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], (array) $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailPageIsWrong(): void
    {
        $page = -1;
        $pageItems = 100;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .implode(',', self::GROUP_ID)
                ."?page={$page}"
                ."&page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], (array) $responseContent->errors->page);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $page = 1;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .implode(',', self::GROUP_ID)
                ."?page={$page}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], (array) $responseContent->errors->page_items);
    }

    /** @test */
    public function itShouldFailPageItemsIsWrong(): void
    {
        $page = 1;
        $pageItems = -1;
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT
                .implode(',', self::GROUP_ID)
                ."?page={$page}"
                ."?page_items={$pageItems}",
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['page_items'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['greater_than'], (array) $responseContent->errors->page_items);
    }
}
