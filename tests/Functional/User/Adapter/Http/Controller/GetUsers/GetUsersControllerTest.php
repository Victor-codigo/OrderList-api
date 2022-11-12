<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\GetUsers;

use Common\Domain\Response\RESPONSE_STATUS;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Domain\Model\User;

class GetUsersControllerTest extends WebClientTestCase
{
    private const ENDPOINT = '/api/v1/users';
    private const METHOD = 'GET';
    private const USER_NAME = 'email.already.exists@host.com';
    private const USER_PASSWORD = '$2y$04$A5XxYKU/w5tpX6BY9Kpf9uDrXY3seHrYwmx70qZ0DWyp0Lmvfo29q';
    private const USERS_NUM_MAX = 50;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function getUsersIds(): array
    {
        return [
            '1befdbe2-9c14-42f0-850f-63e061e33b8f',
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
        ];
    }

    private function getUsersIdsWrong()
    {
        return [
            '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            '1fcab788-0def-4e56-b441-935361678da9',
            'b0bcc444-5bcc-460f-8c47-8085b39ffd3d',
        ];
    }

    /** @test */
    public function itShouldReturnTwoUsers(): void
    {
        $usersIds = $this->getUsersIds();
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users found', $responseContent->message);
        $this->assertCount(count($usersIds), $responseContent->data);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $users = $userRepository->findBy(['id' => $usersIds]);
        $usersNames = array_map(fn (User $user) => $user->getName()->getValue(), $users);

        foreach ($responseContent->data as $user) {
            $this->assertObjectHasAttribute('id', $user);
            $this->assertObjectHasAttribute('name', $user);
            $this->assertObjectHasAttribute('image', $user);

            $this->assertContains($user->id, $usersIds);
            $this->assertContains($user->name, $usersNames);
            $this->assertNull($user->image);
        }
    }

    /** @test */
    public function itShouldFailNoUsersSent(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /** @test */
    public function itShouldFailUsersIdWrong(): void
    {
        $usersIds = $this->getUsersIds();
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode('AAA,', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [0], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error getting users', $responseContent->message);

        $this->assertSame([['uuid_too_long']], $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUsersIdNotFound(): void
    {
        $usersIds = $this->getUsersIdsWrong();
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], [], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users not found', $responseContent->message);
    }
}
