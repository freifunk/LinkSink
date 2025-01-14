<?php

namespace App\Service;

use App\Entity\Tag;
use App\Entity\Link;
use App\Repository\TagRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class TagService
{
    private EntityManagerInterface $entityManager;
    private TagRepository $tagRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(EntityManagerInterface $entityManager, TagRepository $tagRepository, CategoryRepository $categoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllTags(): array
    {
        return $this->tagRepository->findAll();
    }

    public function getTagBySlug(string $slug): ?Tag
    {
        return $this->tagRepository->findOneBy(['slug' => $slug]);
    }

    public function getLinksByTag(Tag $tag)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e')
            ->from(Link::class, 'e')
            ->join('e.tags', 't', 'WITH', $qb->expr()->in('t.id', $tag->getId()))
            ->where('e.deleted IS NULL')
            ->orderBy('e.pubdate', 'desc');
        return $qb->getQuery()->getResult();
    }

    public function searchTags(string $query)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t')
            ->from(Tag::class, 't')
            ->where('t.name LIKE :tag')
            ->orderBy('t.name')
            ->setParameter('tag', sprintf('%%%s%%', strtolower($query)));
        return $qb->getQuery()->getResult();
    }

    public function deleteTag(Tag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }

    public function getYears(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.pubyear')
            ->from(Link::class, 'e')
            ->orderBy('e.pubyear', 'desc')
            ->groupBy('e.pubyear');
        return $qb->getQuery()->getResult();
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
}