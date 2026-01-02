<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testHomePageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testApplicationListIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/application/');

        $this->assertResponseIsSuccessful();
    }
}
