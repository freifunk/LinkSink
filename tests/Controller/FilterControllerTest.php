<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FilterControllerTest extends WebTestCase
{
    private static KernelBrowser $client;

    public static function setUpBeforeClass(): void
    {
        self::ensureKernelShutdown();
        self::$client = static::createClient();
        $entityManager = self::$client->getContainer()->get('doctrine')->getManager();

        // Create and persist the category
        $category = new Category();
        $category->setName('Category 1');
        $category->setSlug('category-1');
        $entityManager->persist($category);
        $entityManager->flush();
        $entityManager->clear();
    }

    public static function tearDownAfterClass(): void
    {
        $manager = self::$client->getContainer()->get('doctrine')->getManager();
        $manager->getConnection()->executeQuery('DELETE FROM category');
    }


    public function testShowActionWithValidCategory()
    {
        $crawler = self::$client->request('GET', '/filter/s/category-1.html');

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('div.alert', '(0 gefundene Links)');
    }

    public function testShowActionWithInvalidCategory()
    {
        $crawler = self::$client->request('GET', '/filter/s/category-999');

        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    public function testGetRssFeed()
    {
        $crawler = self::$client->request('GET', '/filter/s/category-1.rss');

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertStringContainsString('<rss', self::$client->getResponse()->getContent());
        $this->assertStringContainsString('<channel>', self::$client->getResponse()->getContent());
        $this->assertStringContainsString('<title>Gesammelte Links zu Freifunk</title>', self::$client->getResponse()->getContent());
    }
}