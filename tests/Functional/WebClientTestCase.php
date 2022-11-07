<?php

declare(strict_types=1);

namespace Test\Functional;

use Common\Adapter\Jwt\JwtLexikAdapter;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WebClientTestCase extends WebTestCase
{
    protected const CONTENT_TYPE_ALLOWED = 'application/json';
    protected const LOGIN_URL = '/api/v1/en/user/login';
    private const PATH_PRIVATE_KEY = 'tests/Fixtures/JwtKey/private.pem';

    protected KernelBrowser|null $client = null;

    protected function getNewClient(): KernelBrowser
    {
        $this->client = static::createClient();
        $this->client->setServerParameters([
            'CONTENT_TYPE' => static::CONTENT_TYPE_ALLOWED,
            'HTTP_ACCEPT' => static::CONTENT_TYPE_ALLOWED,
        ]);

        return $this->client;
    }

    protected function getNewClientAuthenticated(string $userName, string $password): KernelBrowser
    {
        if (null !== $this->client) {
            return $this->client;
        }

        $this->client = $this->getNewClient();
        $this->client->request(
            method: 'POST',
            uri: self::LOGIN_URL,
            content: json_encode([
              'username' => $userName,
              'password' => $password,
            ])
        );

        return $this->client;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->client
            ->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function generateToken(array $data, float $expire = 3600): string
    {
        $encoder = new JwtLexikAdapter(file_get_contents(self::PATH_PRIVATE_KEY));

        return $encoder->encode($data, $expire);
    }

    protected function assertResponseStructureIsOk(Response $response, array $data = [], array $errors = [], int $responseCode = Response::HTTP_OK, string $contentType = self::CONTENT_TYPE_ALLOWED)
    {
        $content = json_decode($response->getContent());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($responseCode, $response->getStatusCode());
        $this->assertSame($contentType, $response->headers->get('Content-Type'));

        $this->assertObjectHasAttribute('status', $content);
        $this->assertObjectHasAttribute('message', $content);
        $this->assertObjectHasAttribute('data', $content);
        $this->assertObjectHasAttribute('errors', $content);

        if (empty($data)) {
            $this->assertEmpty($content->data);
        }

        if (!empty($data)) {
            $this->assertIsObject($content->data);
        }

        if (empty($errors)) {
            $this->assertEmpty($content->errors);
        }

        if (!empty($errors)) {
            $this->assertIsObject($content->errors);
        }

        foreach ($data as $item) {
            $this->assertObjectHasAttribute($item, $content->data);
        }

        foreach ($errors as $item) {
            $this->assertObjectHasAttribute($item, $content->errors);
        }
    }

    protected function assertRowDoesntExistInDataBase(string $columnName, mixed $value, string $entityClassName): void
    {
        $entityManager = $this->getEntityManager();
        $repository = $entityManager->getRepository($entityClassName);
        $registry = $repository->findOneBy([$columnName => $value]);

        $this->assertNull($registry);
    }

    protected function assertEmailIsSent(string $to): void
    {
        $this->assertQueuedEmailCount(1);
        $email = $this->getMailerMessage(0);
        $this->assertNotNull($email);
        $this->assertEmailHeaderSame($email, 'To', $to);
    }

    protected function assertEmailIsNotSent(): void
    {
        $this->assertQueuedEmailCount(0);
    }
}
