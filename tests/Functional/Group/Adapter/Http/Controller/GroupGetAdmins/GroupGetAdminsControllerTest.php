<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupGetAdmins;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupGetAdminsControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/groups/admins/';
    private const string METHOD = 'GET';
    private const string GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string GROUP_USER_ADMIN_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string GROUP_USER_EMAIL_NOT_BELONGS_TO_THE_GROUP = 'email.other.active@host.com';
    private const string GROUP_USER_PASSWORD_NOT_BELONGS_TO_THE_GROUP = '123456';

    #[Test]
    public function itShouldGetTheAdminsOfTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['is_admin', 'admins'], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Admins of the group', $responseContent->message);

        $this->assertTrue(property_exists($responseContent->data, 'admins'));
        $this->assertTrue(property_exists($responseContent->data, 'is_admin'));
        $this->assertCount(1, $responseContent->data->admins);

        $this->assertContainsEquals(self::GROUP_USER_ADMIN_ID, $responseContent->data->admins);
    }

    #[Test]
    public function itShouldFailGettingTheAdminsOfTheGroupGroupIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'group-id-wrong'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->group_id);
    }

    #[Test]
    public function itShouldFailGettingTheAdminsOfTheGroupGroupIdNotFound(): void
    {
        $groupId = '7989382f-8f91-476e-87fd-f4cf68e7e314';
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.$groupId
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not Found', $responseContent->message);

        $this->assertEquals('Group not Found', $responseContent->errors->group_not_found);
    }

    #[Test]
    public function itShouldFailGettingTheAdminsOfTheGroupUserDoesNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticated(self::GROUP_USER_EMAIL_NOT_BELONGS_TO_THE_GROUP, self::GROUP_USER_PASSWORD_NOT_BELONGS_TO_THE_GROUP);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::GROUP_ID
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You have not permissions', $responseContent->message);

        $this->assertEquals('You have not permissions', $responseContent->errors->permissions);
    }

    #[Test]
    public function itShouldFailGettingTheAdminsOfTheGroupUserNotAuthorized(): void
    {
        $client = $this->getNewClientNoAuthenticated();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.self::GROUP_ID
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
