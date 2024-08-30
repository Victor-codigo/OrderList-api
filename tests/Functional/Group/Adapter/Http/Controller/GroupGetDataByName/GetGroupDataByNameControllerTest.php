<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupGetDataByName;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GetGroupDataByNameControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/groups/data/name/';
    private const string METHOD = 'GET';

    private function getGroupData(): Group
    {
        return Group::fromPrimitives(
            'fdb242b4-bac8-4463-88d0-0941bb0beee0',
            'GroupOne',
            GROUP_TYPE::GROUP,
            'This is a group of users',
            'image_of_group_type_group'
        );
    }

    private function assertGroupDataIsOk(Group $groupDataExpected, \stdClass $groupDataActual): void
    {
        $pathImageGroup = static::getContainer()->getParameter('group.public.image.path');
        $appProtocolAndDomain = static::getContainer()->getParameter('common.app.protocolAndDomain');

        $this->assertTrue(property_exists($groupDataActual, 'group_id'));
        $this->assertTrue(property_exists($groupDataActual, 'type'));
        $this->assertTrue(property_exists($groupDataActual, 'name'));
        $this->assertTrue(property_exists($groupDataActual, 'description'));
        $this->assertTrue(property_exists($groupDataActual, 'image'));
        $this->assertTrue(property_exists($groupDataActual, 'created_on'));

        $this->assertEquals($groupDataExpected->getId()->getValue(), $groupDataActual->group_id);
        $this->assertEquals(GROUP_TYPE::GROUP === $groupDataExpected->getType()->getValue() ? 'group' : 'user', $groupDataActual->type);
        $this->assertEquals($groupDataExpected->getName()->getValue(), $groupDataActual->name);
        $this->assertEquals($groupDataExpected->getDescription()->getValue(), $groupDataActual->description);
        $this->assertEquals($appProtocolAndDomain.$pathImageGroup.'/'.$groupDataExpected->getImage()->getValue(), $groupDataActual->image);
    }

    #[Test]
    public function itShouldGetGroupsDataByName(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $groupData = $this->getGroupData();

        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.$groupData->getName()->getValue()
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [
            'group_id',
            'name',
            'description',
            'image',
            'created_on',
        ], [], Response::HTTP_OK);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('Group data', $responseContent->message);

        $this->assertGroupDataIsOk($groupData, $responseContent->data);
    }

    #[Test]
    public function itShouldFailGettingGroupsDataByNameNotValid(): void
    {
        $client = $this->getNewClientAuthenticatedUser();

        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'NameNotValid--'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_name'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['alphanumeric_with_whitespace'], $responseContent->errors->group_name);
    }

    #[Test]
    public function itShouldFailGettingGroupsDataByNameNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();

        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'NameNotFound'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['group_not_found'], Response::HTTP_NOT_FOUND);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Group not found', $responseContent->message);
    }

    #[Test]
    public function itShouldFailGettingGroupsDataByNameUserNotBelongsToTheGroup(): void
    {
        $client = $this->getNewClientAuthenticatedAdmin();

        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'GroupTwo'
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['permissions'], Response::HTTP_UNAUTHORIZED);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('You not belong to the group', $responseContent->message);
    }
}
