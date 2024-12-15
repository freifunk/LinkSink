<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Link;
use App\Repository\CategoryRepository;
use App\Service\LinkService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class LinkServiceTest extends TestCase
{
    private MockObject $entityManager;
    private MockObject $slugger;
    private LinkService $linkService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->slugger = $this->createMock(SluggerInterface::class);
        $this->linkService = new LinkService($this->entityManager, $this->slugger);
    }

    public function testSaveLink(): void
    {
        $uniqueId = uniqid();
        $request = new Request([
            'ls_pubdate' => '2024-12-12',
            'ls_url' => 'http://example.com',
            'ls_description' => 'Test description ' . $uniqueId,
            'ls_title' => 'Test Title ' . $uniqueId,
            'ls_category' => 'test-category-' . $uniqueId,
        ]);

        $link = new Link();
        $category = new Category();
        $category->setSlug('test-category-' . $uniqueId);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'test-category-' . $uniqueId])
            ->willReturn($category);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Category::class)
            ->willReturn($categoryRepository);

        $this->slugger->expects($this->once())
            ->method('slug')
            ->with('Test Title ' . $uniqueId)
            ->willReturn(new UnicodeString('test-title-' . $uniqueId));

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($link);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->linkService->saveLink($request, $link);

        $this->assertEquals('2024-12-12', $link->getPubdate()->format('Y-m-d'));
        $this->assertEquals('http://example.com', $link->getUrl());
        $this->assertEquals('Test description ' . $uniqueId, $link->getDescription());
        $this->assertEquals('Test Title ' . $uniqueId, $link->getTitle());
        $this->assertEquals('test-title-' . $uniqueId, $link->getSlug());
        $this->assertEquals($category, $link->getCategory());
    }

    public function testSaveLinkWithNonExistentCategory(): void
    {
        $request = new Request([
            'ls_pubdate' => '2024-12-12',
            'ls_url' => 'http://example.com',
            'ls_description' => 'Test description',
            'ls_title' => 'Test Title',
            'ls_category' => 'non-existent-category',
        ]);

        $link = new Link();

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'non-existent-category'])
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Category::class)
            ->willReturn($categoryRepository);

        $this->expectException(\InvalidArgumentException::class);
        $this->linkService->saveLink($request, $link);
    }
}