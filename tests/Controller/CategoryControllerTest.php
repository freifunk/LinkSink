<?php

// tests/Controller/CategoryControllerTest.php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private static KernelBrowser $client;
    public static function setUpBeforeClass(): void
    {
        self::ensureKernelShutdown();
        self::$client = static::createClient();
    }

    protected function tearDown(): void
    {
        $manager = self::$client->getContainer()->get('doctrine')->getManager();
        $manager->getConnection()->executeQuery('DELETE FROM category');
    }

    private function createCategory($client, $name)
    {
        $crawler = self::$client->request('GET', '/kategorie/neu');
        $form = $crawler->selectButton('Speichern')->form();
        $form['name'] = $name;
        self::$client->submit($form);
        self::$client->followRedirect();
    }
    public function testIndex()
    {
        $crawler = self::$client->request('GET', '/kategorie/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Kategorien');
    }

    public function testShow()
    {
        $crawler = self::$client->request('GET', '/kategorie/s/some-category.html');

        $this->assertResponseRedirects('/filter/s/some-category');
    }

    public function testEdit()
    {
        $this->createCategory(self::$client, 'New Category');

        $crawler = self::$client->request('GET', '/kategorie/new-category/bearbeiten');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Speichern')->form();
        $form['name'] = 'Updated Category';
        self::$client->submit($form);
        $this->assertResponseRedirects('/kategorie/');
    }


    public function testCreate()
    {
        $crawler = self::$client->request('GET', '/kategorie/neu');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Speichern')->form();
        $form['name'] = 'New Category';

        self::$client->submit($form);
        $this->assertResponseRedirects('/kategorie/');
    }

    public function testDelete()
    {
        $this->createCategory(self::$client, 'Category to Delete');

        $crawler = self::$client->request('GET', '/kategorie/category-to-delete/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Kategorie löschen');

        $form = $crawler->selectButton('Löschen')->form();
        self::$client->submit($form);
        $this->assertResponseRedirects('/kategorie/?deletedname=Category%20to%20Delete');
    }
}