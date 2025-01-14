<?php

namespace App\Controller;

use App\Service\FilterService;
use Eko\FeedBundle\Feed\FeedManager;
use Eko\FeedBundle\Field\Item\ItemField;
use Eko\FeedBundle\Field\Item\MediaItemField;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/filter")]
class FilterController extends AbstractController
{
    private FilterService $filterService;
    private FeedManager $feedManager;

    public function __construct(FilterService $filterService, FeedManager $feedManager)
    {
        $this->filterService = $filterService;
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
        $myCategory = $this->filterService->findCategoryBySlug($category);
        $allCategories = $this->filterService->findAllCategories();
        $allTags = $this->filterService->findAllTags();

        if ($category && !$myCategory) {
            throw $this->createNotFoundException('Unable to find category entity.');
        }

        $myTag = $this->filterService->findTagBySlug($tag);

        if ($tag && !$myTag) {
            throw $this->createNotFoundException('Unable to find tag entity.');
        }

        $years = $this->filterService->findAllYears();
        $entities = $this->filterService->findLinks($myCategory ? $myCategory->getId() : null, $year != '' ? $year : null, $myTag ? $myTag->getId() : null);

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