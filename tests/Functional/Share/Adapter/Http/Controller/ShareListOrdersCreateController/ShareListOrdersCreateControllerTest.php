<?php

declare(strict_types=1);

namespace Test\Functional\Share\Adapter\Http\Controller\ShareListOrdersCreateController;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Notification\Adapter\Database\Orm\Doctrine\Repository\NotificationRepository;
use Notification\Domain\Model\Notification;
use PHPUnit\Framework\Attributes\Test;
use Share\Adapter\Database\Orm\Doctrine\Repository\ShareRepository;
use Share\Domain\Model\Share;
use Symfony\Component\HttpFoundation\Response;
use Test\Functional\WebClientTestCase;

class ShareListOrdersCreateControllerTest extends WebClientTestCase
{
    use ReloadDatabaseTrait;

    private const string ENDPOINT = '/api/v1/share/list-orders';
    private const string METHOD = 'POST';

    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const string LIST_ORDERS_ID_NOT_REGISTERED = 'f78afa7d-7d4a-413c-b143-e1155129c39c';

    private function getShareRepository(): ShareRepository
    {
        return $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Share::class);
    }

    private function getNotificationsRepository(): NotificationRepository
    {
        return $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Notification::class);
    }

    #[Test]
    public function itShouldCreateNewListOrdersShare(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, ['list_orders_id'], [], Response::HTTP_CREATED);
        $this->assertEquals(RESPONSE_STATUS::OK->value, $responseContent->status);
        $this->assertSame('List orders shared', $responseContent->message);

        $shareRepository = $this->getShareRepository();
        $notificationRepository = $this->getNotificationsRepository();

        /** @var Share $sharedExpected */
        $sharedExpected = $shareRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier($responseContent->data->list_orders_id)]);
        $this->assertEquals(self::LIST_ORDERS_ID, $sharedExpected->getListOrdersId());
        $this->assertEquals(self::USER_ID, $sharedExpected->getUserId());

        /** @var Notification $notificationsExpected */
        $notificationsExpected = $notificationRepository->findOneBy([
            'userId' => ValueObjectFactory::createIdentifier(self::USER_ID),
            'type' => ValueObjectFactory::createNotificationType(NOTIFICATION_TYPE::SHARE_LIST_ORDERS_CREATED),
        ]);
        $this->assertInstanceOf(Notification::class, $notificationsExpected);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['not_blank', 'not_null'], $responseContent->errors->list_orders_id);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => 'Wrong id',
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_id'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('Error', $responseContent->message);

        $this->assertEquals(['uuid_invalid_characters'], $responseContent->errors->list_orders_id);
    }

    #[Test]
    public function itShouldFailListOrdersIdNotFound(): void
    {
        $client = $this->getNewClientAuthenticatedUser();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => self::LIST_ORDERS_ID_NOT_REGISTERED,
            ])
        );

        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertResponseStructureIsOk($response, [], ['list_orders_not_found'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals(RESPONSE_STATUS::ERROR->value, $responseContent->status);
        $this->assertSame('List orders not found', $responseContent->message);

        $this->assertEquals('List orders not found', $responseContent->errors->list_orders_not_found);
    }

    #[Test]
    public function itShouldFailUserNotPermissions(): void
    {
        $client = $this->getNewClientNoAuthenticated();
        $client->request(
            method: self::METHOD,
            uri: self::ENDPOINT,
            content: json_encode([
                'list_orders_id' => 'Wrong id',
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
