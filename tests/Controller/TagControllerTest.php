<?php

namespace App\Tests\Controller;

use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
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
        $manager->getConnection()->executeQuery('DELETE FROM tags');
    }

    private function generateRandomString(int $length = 10): string
    {
        return substr(str_shuffle(str_repeat($x = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    private function createTag(string $name, string $slug): void
    {
        $entityManager = self::$client->getContainer()->get('doctrine')->getManager();
        $tag = new Tag();
        $tag->setName($name);
        $tag->setSlug($slug);
        $entityManager->persist($tag);
        $entityManager->flush();
        $entityManager->clear();
    }
    public function testIndexAction()
    {
        $crawler = self::$client->request('GET', '/tags/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tags');
    }

    public function testQueryAction()
    {
        self::$client->request(
            'GET',
            '/tags/query/?q=sample',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson(self::$client->getResponse()->getContent());
    }

    public function testShowAction()
    {
        $title = $this->generateRandomString();
        $slug = $this->generateRandomString();
        $this->createTag($title, $slug);

        $crawler = self::$client->request('GET', '/tags/' . $slug . '.html');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert', 'gefundene Links');
    }

    public function testDeleteAction()
    {
        $title = $this->generateRandomString();
        $slug = $this->generateRandomString();
        $this->createTag($title, $slug);

        $crawler = self::$client->request('GET', '/tags/' . $slug . '/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert', 'Soll das Tag');
    }

    public function testDeleteConfirmedAction()
    {
        $title = $this->generateRandomString();
        $slug = $this->generateRandomString();
        $this->createTag($title, $slug);

        self::$client->request('POST', '/tags/' . $slug . '/deleteconfirmed');

        $this->assertResponseRedirects('/tags/?deletedname=' . $title);
    }
}