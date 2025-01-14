<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LinkControllerTest extends WebTestCase
{
    private static KernelBrowser $setupClient;
    private static Category $category;

    public static function setUpBeforeClass(): void
    {
        self::$setupClient = static::createClient();
        $entityManager = self::$setupClient->getContainer()->get('doctrine')->getManager();

        // Create and persist the category
        self::$category = new Category();
        self::$category->setName('Test Category');
        self::$category->setSlug('test-category');
        $entityManager->persist(self::$category);
        $entityManager->flush();
        $entityManager->clear();
    }

    public static function tearDownAfterClass(): void
    {
        self::$setupClient = static::createClient();
        $manager = self::$setupClient->getContainer()->get('doctrine')->getManager();
        $manager->getConnection()->executeQuery('DELETE FROM links');
        $manager->getConnection()->executeQuery('DELETE FROM category');
        self::$setupClient->getKernel()->shutdown();
    }

    protected function setUp(): void
    {
        try {
            self::$setupClient->getContainer();
        } catch (LogicException $e) {
            self::$setupClient = static::createClient();
        }
    }

    private function createLink($title): void
    {
        $crawler = self::$setupClient->request('GET', '/links/neu');
        $form = $crawler->selectButton('Speichern')->form();
        $form['ls_title'] = $title;
        $form['ls_url'] = 'http://example.com';
        $form['ls_description'] = 'Test description';
        $form['ls_pubdate'] = "2024-12-12";
        $form['ls_category'] = self::$category->getSlug();
        self::$setupClient->submit($form);
    }

    public function testIndex()
    {
        $crawler = self::$setupClient->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', 'Freifunk LinkSink');
    }

    public function testShow()
    {
        $this->createLink('Test Link');
        $crawler = self::$setupClient->request('GET', '/links/test-link');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', 'Freifunk LinkSink');
    }

    public function testCreate()
    {
        $crawler = self::$setupClient->request('GET', '/links/neu');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Speichern')->form();
        $form['ls_title'] = 'New Link';
        $form['ls_url'] = 'http://example.com';
        $form['ls_description'] = 'Test description';
        $form['ls_category'] = self::$category->getSlug();
        $form['ls_pubdate'] = "2024-12-12";

        self::$setupClient->submit($form);
        $this->assertResponseRedirects('/links/new-link');
    }

    public function testEdit()
    {
        $this->createLink('Link to Edit');
        $crawler = self::$setupClient->request('GET', '/links/link-to-edit/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Speichern')->form();
        $form['ls_title'] = 'Updated Link';
        self::$setupClient->submit($form);
        $this->assertResponseRedirects('/links/updated-link');
    }

    public function testDelete()
    {
        $this->createLink('Link to Delete');
        $crawler = self::$setupClient->request('GET', '/links/link-to-delete/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Lösche Eintrag');

        $form = $crawler->selectButton('Löschen')->form();
        self::$setupClient->submit($form);
        $this->assertResponseRedirects('/?deletedtitle=Link%20to%20Delete');
    }
}