<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Link;
use App\Entity\Enclosure;
use App\Entity\Tag;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class LinkService
{
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    public function saveLink(Request $request, Link $entity): void
    {
        $pubdate = new DateTime($request->get('ls_pubdate'));
        $entity->setPubdate($pubdate);
        $entity->setPubyear($pubdate->format("Y"));
        $entity->setGuid(filter_var($request->get('ls_url'), FILTER_SANITIZE_URL));
        $entity->setDescription(htmlspecialchars($request->get('ls_description'), ENT_QUOTES, 'UTF-8'));
        $entity->setTitle(filter_var($request->get('ls_title'), FILTER_SANITIZE_SPECIAL_CHARS));
        $entity->setUrl(filter_var($request->get('ls_url'), FILTER_SANITIZE_URL));

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['slug' => $request->get('ls_category')]);
        if (!$category) {
            throw new \InvalidArgumentException('Category not found');
        }
        $entity->setCategory($category);

        $slug = $this->slugger->slug($entity->getTitle())->lower();
        $entity->setSlug($slug);

        if ($request->get('ls_enclosureurl')) {
            $enclosureRepository = $this->entityManager->getRepository(Enclosure::class);
            $enclosure = $enclosureRepository->find($request->get('ls_enclosureid')) ?? new Enclosure();
            $info = $this->getUrlHeader($request->get('ls_enclosureurl'));
            $enclosure->setUrl(filter_var($request->get('ls_enclosureurl'), FILTER_SANITIZE_URL));
            $enclosure->setLength($info['download_content_length'] ?? $request->get('ls_enclosurelength'));
            $enclosure->setType($info['content_type'] ?? $request->get('ls_enclosuretype') ?? 'application/octet-stream');

            $this->entityManager->persist($enclosure);
            $entity->setEnclosure($enclosure);
        }

        $tags = $request->get('ls_tags');
        if (!empty($tags)) {
            $tagRepository = $this->entityManager->getRepository(Tag::class);
            $entity->clearTags();
            foreach (explode(',', $tags) as $tag) {
                $tag = trim(filter_var($tag, FILTER_SANITIZE_SPECIAL_CHARS));
                $tagEntity = $tagRepository->findOneBy(['name' => $tag]) ?? (new Tag())->setName($tag)->setSlug($this->slugger->slug($tag)->lower());
                $this->entityManager->persist($tagEntity);
                $entity->addTag($tagEntity);
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function markLinkAsDeleted(Link $link): void
    {
        $link->setDeletedAt(new DateTime());
        $link->setDeleted(true);
        $this->entityManager->persist($link);
        $this->entityManager->flush();
    }

    public function getAllCategories(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }

    public function getAllTags(): array
    {
        return $this->entityManager->getRepository(Tag::class)->findAllOrderedBySlug();
    }

    public function getLinkBySlug(string $slug): ?Link
    {
        return $this->entityManager->getRepository(Link::class)->findOneBy(['slug' => $slug]);
    }

    public function getLinks(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e')
            ->from(Link::class, 'e')
            ->where('e.deleted IS NULL')
            ->orderBy('e.pubdate', 'desc');
        return $qb->getQuery()->getResult();
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

    public function getCategoryBySlug(string $slug): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->findOneBy(['slug' => $slug]);
    }
    private function getUrlHeader(string $url): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_UPLOAD);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        return $info;
    }
}