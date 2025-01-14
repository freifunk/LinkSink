<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Link;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Service\FilterService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterServiceTest extends TestCase
{
    private MockObject $entityManager;
    private MockObject $categoryRepository;
    private MockObject $tagRepository;
    private FilterService $filterService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->tagRepository = $this->createMock(TagRepository::class);

        $this->filterService = new FilterService($this->entityManager, $this->categoryRepository, $this->tagRepository);
    }

    public function testFindCategoryBySlug()
    {
        $category = new Category();
        $category->setSlug('category-1');

        $this->categoryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'category-1'])
            ->willReturn($category);

        $result = $this->filterService->findCategoryBySlug('category-1');
        $this->assertSame($category, $result);
    }

    public function testFindAllCategories()
    {
        $categories = [new Category(), new Category()];

        $this->categoryRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($categories);

        $result = $this->filterService->findAllCategories();
        $this->assertSame($categories, $result);
    }

    public function testFindTagBySlug()
    {
        $tag = new Tag();
        $tag->setSlug('tag-1');

        $this->tagRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'tag-1'])
            ->willReturn($tag);

        $result = $this->filterService->findTagBySlug('tag-1');
        $this->assertSame($tag, $result);
    }

    public function testFindAllTags()
    {
        $tags = [new Tag(), new Tag()];

        $this->tagRepository->expects($this->once())
            ->method('findAllOrderedBySlug')
            ->willReturn($tags);

        $result = $this->filterService->findAllTags();
        $this->assertSame($tags, $result);
    }

    public function testFindAllYears()
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('e.pubyear')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('App\Entity\Link', 'e')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('e.pubyear', 'desc')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('groupBy')
            ->with('e.pubyear')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn(['2023', '2022']);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $result = $this->filterService->findAllYears();
        $this->assertSame(['2023', '2022'], $result);
    }

    public function testFindLinks()
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('e')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('App\Entity\Link', 'e')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('Join')
            ->with('e.category', 'c', 'WITH', $queryBuilder->expr()->in('c.id', -1))
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with('e.deleted IS NULL')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('e.pubdate', 'desc')
            ->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([new Link(), new Link()]);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $result = $this->filterService->findLinks('category', null, null);
        $this->assertCount(2, $result);
    }
}