<?php

declare(strict_types=1);

namespace Test\Functional\Group\Adapter\Http\Controller\GroupCreate;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class GroupCreateTest extends WebClientTestCase
{
    use RefreshDatabaseTrait;

    private const ENDPOINT = '/api/v1/groups';
    private const METHOD = 'POST';
    private const USER_NAME = 'email.already.active@host.com';
    private const USER_PASSWORD = '123456';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function itShouldCreateAGroup(): void
    {
        $this->client = $this->getNewClientAuthenticated(self::USER_NAME, self::USER_PASSWORD);
        $this->client->request(
            method: self::METHOD,
            uri: self::ENDPOINT.'?SESSION_XDEBUG=VSCODE',
            content: json_encode([
                'name' => 'GroupName',
                'description' => 'Group description',
            ])
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['id'], [], Response::HTTP_CREATED);
    }
}
