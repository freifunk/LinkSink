<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Repository\CategoryRepository;
use App\Service\TagService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TagServiceTest extends TestCase
{
    private MockObject $entityManager;
    private MockObject $tagRepository;
    private MockObject $categoryRepository;
    private TagService $tagService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);

        $this->tagService = new TagService($this->entityManager, $this->tagRepository, $this->categoryRepository);
    }

    public function testGetAllTags()
    {
        $tags = [new Tag(), new Tag()];
        $this->tagRepository->method('findAll')->willReturn($tags);

        $result = $this->tagService->getAllTags();
        $this->assertCount(2, $result);
    }

    public function testGetTagBySlug()
    {
        $tag = new Tag();
        $this->tagRepository->method('findOneBy')->with(['slug' => 'test-slug'])->willReturn($tag);

        $result = $this->tagService->getTagBySlug('test-slug');
        $this->assertSame($tag, $result);
    }

    public function testGetLinksByTag()
    {
        $tag = new Tag();
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $queryBuilder->method('join')->willReturn($queryBuilder);
        $queryBuilder->method('where')->willReturn($queryBuilder);
        $queryBuilder->method('orderBy')->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getResult')->willReturn([]);

        $result = $this->tagService->getLinksByTag($tag);
        $this->assertIsArray($result);
    }

    public function testSearchTags()
    {
        $tags = [new Tag(), new Tag()];
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturn($queryBuilder);
        $queryBuilder->method('from')->willReturn($queryBuilder);
        $queryBuilder->method('where')->willReturn($queryBuilder);
        $queryBuilder->method('orderBy')->willReturn($queryBuilder);
        $queryBuilder->method('setParameter')->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getResult')->willReturn($tags);

        $result = $this->tagService->searchTags('test');
        $this->assertCount(2, $result);
    }

    public function testDeleteTag()
    {
        $tag = new Tag();
        $this->entityManager->expects($this->once())->method('remove')->with($tag);
        $this->entityManager->expects($this->once())->method('flush');

        $this->tagService->deleteTag($tag);
    }

    public function testGetAllCategories()
    {
        $categories = [new Category(), new Category()];
        $this->categoryRepository->method('findAll')->willReturn($categories);

        $result = $this->tagService->getAllCategories();
        $this->assertCount(2, $result);
    }
}