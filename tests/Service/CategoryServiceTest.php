<?php

// tests/Service/CategoryServiceTest.php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class CategoryServiceTest extends TestCase
{
    private $entityManager;
    private $categoryRepository;
    private $slugger;
    private $categoryService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->slugger = $this->createMock(SluggerInterface::class);

        $this->categoryService = new CategoryService(
            $this->entityManager,
            $this->categoryRepository,
            $this->slugger
        );
    }

    protected function verifyNoMoreInteractions(...$mocks)
    {
        foreach ($mocks as $mock) {
            $mock->expects($this->never())->method($this->anything());
        }
    }

    public function testUpdateCategory()
    {
        $category = new Category();
        $category->setName('Old Name');
        $category->setSlug('old-name');

        $this->categoryRepository->method('findOneBy')->willReturn($category);
        $this->slugger->method('slug')->willReturn(new UnicodeString('new-name'));

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($category));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedCategory = $this->categoryService->updateCategory('old-name', 'New Name');

        $this->assertNotNull($updatedCategory);
        $this->assertEquals('New Name', $updatedCategory->getName());
        $this->assertEquals('new-name', $updatedCategory->getSlug());
    }

    public function testCreateCategory()
    {
        $this->slugger->method('slug')->willReturn(new UnicodeString('new-category'));

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));
        $this->entityManager->expects($this->once())
            ->method('flush');
        $newCategory = $this->categoryService->createCategory('New Category');

        $this->assertNotNull($newCategory);
        $this->assertEquals('New Category', $newCategory->getName());
        $this->assertEquals('new-category', $newCategory->getSlug());
    }

    public function testDeleteCategory()
    {
        $category = new Category();
        $category->setName('Category to Delete');
        $category->setSlug('category-to-delete');

        $this->categoryRepository->method('findOneBy')->willReturn($category);
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($category));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $deletedCategory = $this->categoryService->deleteCategory('category-to-delete');

        $this->assertNotNull($deletedCategory);
        $this->assertEquals('Category to Delete', $deletedCategory->getName());
        $this->assertEquals('category-to-delete', $deletedCategory->getSlug());
    }
}