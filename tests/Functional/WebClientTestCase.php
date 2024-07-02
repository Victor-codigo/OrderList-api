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
    protected const string CONTENT_TYPE_ALLOWED = 'application/json';
    protected const string LOGIN_URL = '/api/v1/users/login';
    private const string PATH_PRIVATE_KEY = 'src/Common/Adapter/Framework/Config/JwtKeys/Lexik/private.pem';
    private const string USER_ADMIN_EMAIL = 'email.admin.active@host.com';
    private const string USER_ADMIN_PASSWORD = '123456';
    private const string USER_USER_EMAIL = 'email.already.active@host.com';
    private const string USER_USER_PASSWORD = '123456';

    private static ?KernelBrowser $clientAuthenticatedUser = null;
    private static ?KernelBrowser $clientAuthenticatedAdmin = null;
    protected static ?KernelBrowser $clientNoAuthenticated = null;
    /**
     * @var EntityManager[]
     */
    private array $entityManagerArray = [];

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$clientAuthenticatedAdmin = null;
        static::$clientAuthenticatedUser = null;
        static::$clientNoAuthenticated = null;

        foreach ($this->entityManagerArray as $entityManager) {
            $entityManager->close();
            $entityManager = null;
        }
    }

    protected function getNewClient(): KernelBrowser
    {
        $client = static::createClient();

        $client->setServerParameters([
            'CONTENT_TYPE' => static::CONTENT_TYPE_ALLOWED,
            'HTTP_ACCEPT' => static::CONTENT_TYPE_ALLOWED,
        ]);

        return $client;
    }

    protected function getNewClientAuthenticated(string $userName, string $password): KernelBrowser
    {
        $client = $this->getNewClient();
        $client->request(
            method: 'POST',
            uri: self::LOGIN_URL,
            content: json_encode([
                'username' => $userName,
                'password' => $password,
            ])
        );

        return $client;
    }

    protected function getCurrentClient(): ?KernelBrowser
    {
        if (null !== static::$clientAuthenticatedUser) {
            return static::$clientAuthenticatedUser;
        }

        if (null !== static::$clientAuthenticatedAdmin) {
            return static::$clientAuthenticatedAdmin;
        }

        return static::$clientNoAuthenticated;
    }

    protected function getNewClientAuthenticatedUser(): KernelBrowser
    {
        if (null === static::$clientAuthenticatedUser) {
            static::$clientAuthenticatedUser = $this->getNewClientAuthenticated(self::USER_USER_EMAIL, self::USER_USER_PASSWORD);

            return static::$clientAuthenticatedUser;
        }

        return static::$clientAuthenticatedUser;
    }

    protected function getNewClientAuthenticatedAdmin(): KernelBrowser
    {
        if (null === static::$clientAuthenticatedAdmin) {
            static::$clientAuthenticatedAdmin = $this->getNewClientAuthenticated(self::USER_ADMIN_EMAIL, self::USER_ADMIN_PASSWORD);

            return static::$clientAuthenticatedAdmin;
        }

        return static::$clientAuthenticatedAdmin;
    }

    protected function getNewClientNoAuthenticated(): KernelBrowser
    {
        if (null === static::$clientNoAuthenticated) {
            static::$clientNoAuthenticated = $this->getNewClient();

            return static::$clientNoAuthenticated;
        }

        $this->bootKernel();

        return static::$clientNoAuthenticated;
    }

    protected function getEntityManager(): EntityManager
    {
        $entityManager = $this->getCurrentClient()
            ->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->entityManagerArray[] = $entityManager;

        return $entityManager;
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

        $this->assertTrue(property_exists($content, 'status'));
        $this->assertTrue(property_exists($content, 'message'));
        $this->assertTrue(property_exists($content, 'data'));
        $this->assertTrue(property_exists($content, 'errors'));

        if (empty($data)) {
            $this->assertEmpty($content->data);
        }

        if (!empty($data)) {
            if (is_array($content->data)) {
                $this->assertIsArray($content->data);
                $this->assertCount(count($data), $content->data);
            }

            if (is_object($content->data)) {
                $this->assertIsObject($content->data);
            }
        }

        if (empty($errors)) {
            $this->assertEmpty($content->errors);
            $this->assertCount(count($errors), $content->errors);
        }

        if (!empty($errors)) {
            if (is_array($content->errors)) {
                $this->assertIsArray($content->errors);
            }

            if (is_object($content->errors)) {
                $this->assertIsObject($content->errors);
            }
        }

        foreach ($data as $item) {
            if (is_array($content->data) && is_string($item)) {
                $this->assertTrue(property_exists($content->data, $item));
            }

            if (is_object($content->data)) {
                $this->assertTrue(property_exists($content->data, $item));
            }
        }

        foreach ($errors as $item) {
            if (is_array($content->errors) && is_string($item)) {
                $this->assertArrayHasKey($item, $content->errors);
            }

            if (is_object($content->errors)) {
                $this->assertTrue(property_exists($content->errors, $item));
            }
        }
    }

    protected function assertRowDoesNotExistInDataBase(string $columnName, mixed $value, string $entityClassName): void
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

    protected function removeFolderFiles(string $path): void
    {
        $files = glob($path.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
