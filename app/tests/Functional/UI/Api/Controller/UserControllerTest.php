<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\Api\Controller;

use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $em = self::getContainer()->get('doctrine')->getManager();
        $conn = $em->getConnection();

        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $conn->executeStatement('TRUNCATE TABLE phone_numbers');
        $conn->executeStatement('TRUNCATE TABLE user');
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @param array $payload
     * @return KernelBrowser
     * @throws JsonException
     */
    private function postUser(array $payload): KernelBrowser
    {
        $this->client->request(
            'POST',
            '/v1/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        return $this->client;
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCreateUserReturns202(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $client = $this->postUser([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumbers' => ['+380971234567'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $data);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCreateUserIsPersistedWhenQueueIsSynchronous(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $this->postUser([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumbers' => ['+380971234567', '+380631234567'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $this->client->request('GET', '/v1/api/users/list');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($data['data']);

        $found = array_filter(
            $data['data'],
            fn($u) => $u['firstName'] === $firstName && $u['lastName'] === $lastName
        );

        $this->assertNotEmpty($found);

        $user = array_values($found)[0];

        $this->assertCount(2, $user['phoneNumbers']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCreateUserValidationFailsWithBlankFirstName(): void
    {
        $lastName = 'Шевченко';

        $this->postUser([
            'firstName' => '',
            'lastName' => $lastName,
            'phoneNumbers' => ['+380971234567'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $data);
        $this->assertIsString($data['error']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCreateUserValidationFailsWithEmptyPhoneNumbers(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $this->postUser([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumbers' => [],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testCreateUserValidationFailsWithInvalidPhoneFormat(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $this->postUser([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumbers' => ['not-a-phone'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return void
     */
    public function testCreateUserReturnsBadRequestForInvalidJson(): void
    {
        $this->client->request(
            'POST',
            '/v1/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'not valid json'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return void
     */
    public function testListUsersReturnsOkWithDataKey(): void
    {
        $this->client->request('GET', '/v1/api/users/list');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    /**
     * @return void
     */
    public function testListUsersWithSortingByFirstNameAsc(): void
    {
        $this->client->request('GET', '/v1/api/users/list?sort=firstName&order=ASC');

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return void
     */
    public function testListUsersWithInvalidSortFieldFallsBackToDefault(): void
    {
        $this->client->request('GET', '/v1/api/users/list?sort=nonExistentField&order=DESC');

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testListUsersResponseStructure(): void
    {
        $firstName = 'Іван';
        $lastName = 'Шевченко';

        $this->postUser([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumbers' => ['+380971234567'],
        ]);

        $this->client->request('GET', '/v1/api/users/list');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        if (!empty($data['data'])) {
            $user = $data['data'][0];

            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('firstName', $user);
            $this->assertArrayHasKey('lastName', $user);
            $this->assertArrayHasKey('ip', $user);
            $this->assertArrayHasKey('country', $user);
            $this->assertArrayHasKey('createdAt', $user);
            $this->assertArrayHasKey('phoneNumbers', $user);
        }
    }
}
