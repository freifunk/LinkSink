<?php
// src/Service/CategoryService.php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryService
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
        $this->slugger = $slugger;
    }

    public function updateCategory(string $slug, string $name): ?Category
    {
        $entity = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$entity) {
            return null;
        }

        $entity->setName(filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS));
        $entity->setSlug($this->slugger->slug($entity->getName())->lower());

        if ($entity->isValid()) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }

        return $entity;
    }

    public function createCategory(string $name): Category
    {
        $slug = $this->slugger->slug($name)->lower();
        $entity = $this->categoryRepository->findOneBy(['slug' => $slug]) ?? new Category();
        $entity->setName($name);
        $entity->setSlug($slug);

        if ($entity->isValid()) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }

        return $entity;
    }

    public function deleteCategory(string $slug): ?Category
    {
        $entity = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$entity) {
            return null;
        }

        if ($entity->isValid()) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }

        return $entity;
    }
}