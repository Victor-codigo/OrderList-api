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

    private const string METHOD = 'DELETE';
    private const string ENDPOINT = '/api/v1/users/remove';
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string USER_ID_HAS_NO_GROUP = '1552b279-5f78-4585-ae1b-31be2faabba8';

    private UserRemoveController $object;

    /** @test */
    public function itShouldRemoveTheUserSessionWithAllItsData(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User Removed', $responseContent->message);

        $this->assertEquals(self::USER_ID, $responseContent->data->id);
    }

    /** @test */
    public function itShouldRemoveTheUserSessionHasNoGroupsNoNotifications(): void
    {
        $client = $this->getNewClientAuthenticated('email.other_2.active@host.com', '123456');
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User Removed', $responseContent->message);

        $this->assertEquals(self::USER_ID_HAS_NO_GROUP, $responseContent->data->id);
    }

    /** @test */
    public function itShouldFailUserNotAuthorized(): void
    {
        $client = $this->getNewClientNoAuthenticated();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
