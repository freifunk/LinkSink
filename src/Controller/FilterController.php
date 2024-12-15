<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 20.02.17
 * Time: 22:33
 */

namespace App\Controller;


use App\Entity\Link;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Eko\FeedBundle\Feed\FeedManager;
use Eko\FeedBundle\Field\Item\ItemField;
use Eko\FeedBundle\Field\Item\MediaItemField;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/filter")]
class FilterController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private FeedManager $feedManager;

    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, TagRepository $tagRepository, FeedManager $feedManager)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->feedManager = $feedManager;
    }


    #[Route('/s/{category}.{format}', name: 'category_filter', defaults: ['year' => '', 'tag' => '', 'format' => 'html'], methods: ['GET'])]
    #[Route('/s/{category}/{year}.{format}', name: 'category_filter_year', requirements: ['year' => '\d{4}'], defaults: ['tag' => '', 'format' => 'html'], methods: ['GET'])]
    #[Route('/s/{category}/{year}/{tag}.{format}', name: 'category_filter_year_tag', requirements: ['year' => '\d{4}'], defaults: ['format' => 'html'], methods: ['GET'])]
    #[Route('/s/{category}/{tag}.{format}', name: 'category_filter_tag', requirements: ['tag' => '[A-Za-z0-9\-]+'], defaults: ['year' => '', 'format' => 'html'], methods: ['GET'])]
    #[Route('/y/{year}.{format}', name: 'year_filter', requirements: ['year' => '\d{4}'], defaults: ['tag' => '', 'category' => '', 'format' => 'html'], methods: ['GET'])]
    #[Route('/y/{year}/{tag}.{format}', name: 'year_tag_filter', requirements: ['year' => '\d{4}'], defaults: ['category' => '', 'format' => 'html'], methods: ['GET'])]
    public function showAction(string $category, string $year, string $tag, string $format): Response
    {
        $myCategory = $this->categoryRepository->findOneBy(['slug' => $category]);
        $allCategories = $this->categoryRepository->findAll();
        $allTags = $this->tagRepository->findAllOrderedBySlug();

        if ($category && !$myCategory) {
            throw $this->createNotFoundException('Unable to find category entity.');
        }

        $myTag = $this->tagRepository->findOneBy(['slug' => $tag]);

        if ($tag && !$myTag) {
            throw $this->createNotFoundException('Unable to find tag entity.');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.pubyear')
            ->from('App\Entity\Link', 'e')
            ->orderBy('e.pubyear', 'desc')
            ->groupBy('e.pubyear');
        $years = $qb->getQuery()->getResult();

        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from('App\Entity\Link', 'e')
            ->leftJoin('e.category', 'c', 'WITH', $qb->expr()->in('c.id', $myCategory ? $myCategory->getId() : -1))
            ->leftJoin('e.tags', 't', 'WITH', $qb->expr()->in('t.id', $myTag ? $myTag->getId() : -1))
            ->andWhere('e.deleted IS NULL')
            ->orderBy('e.pubdate', 'desc');

        if ($year) {
            $qb->andWhere('e.pubyear = :year')
                ->setParameter('year', $year);
        }
        /** @var Link[] $entities */
        $entities = $qb->getQuery()->getResult();

        if ($format == 'rss') {
            $feed = $this->feedManager->get('news');
            $feed->addItemField(new ItemField('category', 'getCategoryName'));
            $feed->addItemField(new MediaItemField('getFeedMediaItem'));
            $feed->addFromArray($entities);

            return new Response($feed->render('rss'), 200, ['Content-Type' => 'application/rss+xml']);
        } else {
            return $this->render('link/index.html.twig', [
                'entities' => $entities,
                'tag' => $myTag ? $myTag->getSlug() : '',
                'tags' => $allTags,
                'category' => $myCategory ? $myCategory->getSlug() : '',
                'categories' => $allCategories,
                'year' => $year,
                'years' => $years,
            ]);
        }
    }
}