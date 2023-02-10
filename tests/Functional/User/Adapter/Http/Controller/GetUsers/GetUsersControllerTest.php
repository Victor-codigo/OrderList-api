<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\GetUsers;

use Common\Domain\Response\RESPONSE_STATUS;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class GetUsersControllerTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const ENDPOINT = '/api/v1/users';
    private const METHOD = 'GET';
    private const USER_NAME = 'email.already.active@host.com';
    private const USER_PASSWORD = '123456';
    private const USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const USER_ADMIN_NAME = 'email.admin.active@host.com';
    private const USER_ADMIN_PASSWORD = '123456';

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function getUsersIds(): array
    {
        return [
            '1552b279-5f78-4585-ae1b-31be2faabba8',
            '2606508b-4516-45d6-93a6-c7cb416b7f3f',
            '6df60afd-f7c3-4c2c-b920-e265f266c560',
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

    private function getUsersIdsDeletedOrNotActive()
    {
        return [
            '68e94495-16f0-4acd-adbe-f2b9575e6544', // deleted
            '1befdbe2-9c14-42f0-850f-63e061e33b8f', // not active
        ];
    }

    private function assertValidUserDataPrivate(array $data, array $usersIds, array $usersEmails, array $usersNames, array $usersRoles, array $usersCreatedOn, array $usersImages): void
    {
        foreach ($data as $user) {
            $this->assertCount(6, get_object_vars($user));

            $this->assertObjectHasAttribute('id', $user);
            $this->assertObjectHasAttribute('email', $user);
            $this->assertObjectHasAttribute('name', $user);
            $this->assertObjectHasAttribute('roles', $user);
            $this->assertObjectHasAttribute('createdOn', $user);
            $this->assertObjectHasAttribute('image', $user);

            $this->assertContains($user->id, $usersIds);
            $this->assertContains($user->email, $usersEmails);
            $this->assertContains($user->name, $usersNames);
            $this->assertContainsEquals($user->roles, $usersRoles);
            $this->assertContains($user->createdOn, $usersCreatedOn);
            $this->assertContains($user->image, $usersImages);
        }
    }

    private function assertValidUserDataPublic(array $data, array $usersIds, array $usersNames, array $usersImages): void
    {
        foreach ($data as $user) {
            $this->assertCount(3, get_object_vars($user));

            $this->assertObjectHasAttribute('id', $user);
            $this->assertObjectHasAttribute('name', $user);
            $this->assertObjectHasAttribute('image', $user);

            $this->assertContains($user->id, $usersIds);
            $this->assertContains($user->name, $usersNames);
            $this->assertContains($user->image, $usersImages);
        }
    }

    private function getUserDataFromDataBase(array $usersIds): array
    {
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $users = $userRepository->findBy(['id' => $usersIds]);

        return [
            'usersEmails' => array_map(fn (User $user) => $user->getEmail()->getValue(), $users),
            'usersNames' => array_map(fn (User $user) => $user->getName()->getValue(), $users),
            'usersRoles' => array_map(
                fn (User $user) => array_map(
                    fn (USER_ROLES $rol) => $rol->value,
                    $user->getRoles()->getRolesEmums()),
                $users
            ),
            'usersCreatedOn' => array_map(fn (User $user) => $user->getCreatedOn()->format('Y-m-d H:i'), $users),
            'usersImages' => array_map(fn (User $user) => $user->getProfile()->getImage()->getValue(), $users),
        ];
    }

    /** @test */
    public function itShouldReturnThreeUsers(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $usersIds = $this->getUsersIds();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users found', $responseContent->message);
        $this->assertCount(count($usersIds), $responseContent->data);

        $usersData = $this->getUserDataFromDataBase($usersIds);

        $this->assertValidUserDataPublic(
            $responseContent->data,
            $usersIds,
            $usersData['usersNames'],
            $usersData['usersImages']
        );
    }

    /** @test */
    public function itShouldReturnThreeUsersNotUsersDeletedOrNotActive(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $usersIds = array_merge($this->getUsersIds(), $this->getUsersIdsDeletedOrNotActive());
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users found', $responseContent->message);
        $this->assertCount(count($this->getUsersIds()), $responseContent->data);
    }

    /** @test */
    public function itShouldReturnAnUserDifferentFromItself(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $userId = $this->getUsersIds()[0];
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.$userId,
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users found', $responseContent->message);
        $this->assertCount(1, $responseContent->data);

        $usersData = $this->getUserDataFromDataBase([$userId]);
        $this->assertValidUserDataPublic(
            $responseContent->data,
            [$userId],
            $usersData['usersNames'],
            $usersData['usersImages']
        );
    }

    /** @test */
    public function itShouldReturnThreeUsersForAdminUser(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_ADMIN_NAME, self::USER_ADMIN_PASSWORD);
        $usersIds = $this->getUsersIds();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users found', $responseContent->message);
        $this->assertCount(count($usersIds), $responseContent->data);

        $usersData = $this->getUserDataFromDataBase($usersIds);
        $this->assertValidUserDataPrivate(
            $responseContent->data,
            $usersIds,
            $usersData['usersEmails'],
            $usersData['usersNames'],
            $usersData['usersRoles'],
            $usersData['usersCreatedOn'],
            $usersData['usersImages']
        );
    }

    /** @test */
    public function itShouldReturnUsersOwnData(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.self::USER_ID,
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Users found', $responseContent->message);
        $this->assertCount(1, $responseContent->data);

        $usersData = $this->getUserDataFromDataBase([self::USER_ID]);
        $this->assertValidUserDataPrivate(
            $responseContent->data,
            [self::USER_ID],
            $usersData['usersEmails'],
            $usersData['usersNames'],
            $usersData['usersRoles'],
            $usersData['usersCreatedOn'],
            $usersData['usersImages']
        );
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
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $usersIds = $this->getUsersIds();
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

        $this->assertSame([['uuid_too_long'], ['uuid_too_long']], $responseContent->errors);
    }

    /** @test */
    public function itShouldFailUsersIdNotFound(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $usersIds = $this->getUsersIdsWrong();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertNull($responseContent);
    }

    /** @test */
    public function itShouldFailUsersIdAreDeletedOrNotActived(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $usersIds = $this->getUsersIdsDeletedOrNotActive();
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersIds),
            content: json_encode([])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertNull($responseContent);
    }
}
