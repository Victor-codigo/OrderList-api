<?php

declare(strict_types=1);

namespace Test\Functional\User\Adapter\Http\Controller\UserGetByNameController;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\User\USER_ROLES;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;
use User\Adapter\Http\Controller\UserGetByName\UserGetByNameController;
use User\Domain\Model\User;

class UserGetByNameControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/users/name';
    private const string METHOD = 'GET';

    private UserGetByNameController $object;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return User[]
     */
    private function getUsersData(): array
    {
        return [
            User::fromPrimitives('1befdbe2-9c14-42f0-850f-63e061e33b8f', 'email.already.exists@host.com', '', 'Juanito', [USER_ROLES::USER]),
            User::fromPrimitives('2606508b-4516-45d6-93a6-c7cb416b7f3f', 'email.already.active@host.com', '', 'Maria', [USER_ROLES::USER]),
            User::fromPrimitives('6df60afd-f7c3-4c2c-b920-e265f266c560', 'email.admin.active@host.com', '', 'Admin', [USER_ROLES::ADMIN]),
        ];
    }

    /**
     * @param User[] $users
     */
    private function getRolesString(array $users): array
    {
        $userRoles = [];
        foreach ($users as $user) {
            $roles = $user->getRoles()->getRolesEnums();
            $userRoles[] = array_map(
                fn (USER_ROLES $userRoles) => $userRoles->value,
                $roles
            );
        }

        return $userRoles;
    }

    /** @test */
    public function itShouldGetUserInfoByName(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $userData = $this->getUsersData();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."/{$userData[0]->getName()->getValue()}",
        );
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User found', $responseContent->message);

        $this->assertCount(1, $responseContent->data);
        $this->assertTrue(property_exists($responseContent->data[0], 'id'));
        $this->assertTrue(property_exists($responseContent->data[0], 'name'));
        $this->assertEquals($userData[0]->getId()->getValue(), $responseContent->data[0]->id);
        $this->assertEquals($userData[0]->getName()->getValue(), $responseContent->data[0]->name);
    }

    /** @test */
    public function itShouldGetUserInfoByNameUserSessionAdmin(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();
        $userData = $this->getUsersData();
        $userRoles = $this->getRolesString([$userData[0]]);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."/{$userData[0]->getName()->getValue()}",
        );
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User found', $responseContent->message);

        $this->assertCount(1, $responseContent->data);
        $this->assertTrue(property_exists($responseContent->data[0], 'id'));
        $this->assertTrue(property_exists($responseContent->data[0], 'name'));
        $this->assertTrue(property_exists($responseContent->data[0], 'email'));
        $this->assertTrue(property_exists($responseContent->data[0], 'roles'));
        $this->assertTrue(property_exists($responseContent->data[0], 'created_on'));
        $this->assertEquals($userData[0]->getId()->getValue(), $responseContent->data[0]->id);
        $this->assertEquals($userData[0]->getName()->getValue(), $responseContent->data[0]->name);
        $this->assertEquals($userData[0]->getEmail()->getValue(), $responseContent->data[0]->email);
        $this->assertEquals($responseContent->data[0]->roles, $userRoles[0]);
        $this->assertIsString($responseContent->data[0]->created_on);
    }

    /** @test */
    public function itShouldGetUserInfoByNameUserSessionIsHimself(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $userData = $this->getUsersData();
        $userRoles = $this->getRolesString([$userData[1]]);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."/{$userData[1]->getName()->getValue()}",
        );
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User found', $responseContent->message);

        $this->assertCount(1, $responseContent->data);
        $this->assertTrue(property_exists($responseContent->data[0], 'id'));
        $this->assertTrue(property_exists($responseContent->data[0], 'name'));
        $this->assertTrue(property_exists($responseContent->data[0], 'email'));
        $this->assertTrue(property_exists($responseContent->data[0], 'roles'));
        $this->assertTrue(property_exists($responseContent->data[0], 'created_on'));
        $this->assertEquals($userData[1]->getId()->getValue(), $responseContent->data[0]->id);
        $this->assertEquals($userData[1]->getName()->getValue(), $responseContent->data[0]->name);
        $this->assertEquals($userData[1]->getEmail()->getValue(), $responseContent->data[0]->email);
        $this->assertEquals($responseContent->data[0]->roles, $userRoles[0]);
        $this->assertIsString($responseContent->data[0]->created_on);
    }

    /** @test */
    public function itShouldGetUserInfoByNameManyUsers(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $userData = $this->getUsersData();
        $usersNames = array_map(fn (User $user): ?string => $user->getName()->getValue(), $userData);
        $usersIds = array_map(fn (User $user): ?string => $user->getId()->getValue(), $userData);
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $usersNames),
        );
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0, 1, 2], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User found', $responseContent->message);

        $this->assertCount(count($userData), $responseContent->data);

        foreach ($responseContent->data as $user) {
            $this->assertContainsEquals($user->id, $usersIds);
            $this->assertContainsEquals($user->name, $usersNames);
        }
    }

    /** @test */
    public function itShouldGetUserInfoByName51Users(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $userData = $this->getUsersData();
        $userName = array_fill(0, 50, $userData[0]->getName()->getValue());
        $userName[] = 'UserOut';
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'/'.implode(',', $userName),
        );
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [0], [], Response::HTTP_OK);
        $this->assertSame(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('User found', $responseContent->message);

        $this->assertCount(1, $responseContent->data);
        $this->assertEquals($userData[0]->getId()->getValue(), $responseContent->data[0]->id);
        $this->assertEquals($userData[0]->getName()->getValue(), $responseContent->data[0]->name);
    }

    /** @test */
    public function itShouldFailGettingUserInfoByNameUserNotValid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $userName = 'User not valid+';
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."/{$userName}",
        );
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['users_name'], Response::HTTP_BAD_REQUEST);
        $this->assertSame(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['alphanumeric_with_whitespace'], $responseContent->errors->users_name);
    }

    /** @test */
    public function itShouldFailGettingUserInfoByNameUserNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $userName = 'NotExistsUser';
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT."/{$userName}",
        );
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
