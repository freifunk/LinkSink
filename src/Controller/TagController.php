<?php

namespace App\Controller;

use App\Service\TagService;
use Eko\FeedBundle\Feed\FeedManager;
use Eko\FeedBundle\Field\Item\ItemField;
use Eko\FeedBundle\Field\Item\MediaItemField;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\AcceptHeader;

#[Route('/tags')]
class TagController extends AbstractController
{
    private TagService $tagService;
    private FeedManager $feedManager;

    public function __construct(TagService $tagService, FeedManager $feedManager)
    {
        $this->tagService = $tagService;
        $this->feedManager = $feedManager;
    }

    #[Route('/{slug}.{format}', name: 'tag_show', defaults: ['format' => 'html'], methods: ['GET'])]
    public function showAction(string $slug, string $format): Response
    {
        $allCategories = $this->tagService->getAllCategories();
        $allTags = $this->tagService->getAllTags();
        $tag = $this->tagService->getTagBySlug($slug);

        if (!$tag) {
            throw $this->createNotFoundException('Unable to find tag entity.');
        }

        $entities = $this->tagService->getLinksByTag($tag);

        if ($format == 'rss') {
            $feed = $this->feedManager->get('news');
            $feed->addItemField(new ItemField('category', 'getCategoryName'));
            $feed->addItemField(new MediaItemField('getFeedMediaItem'));
            $feed->addFromArray($entities);

            return new Response($feed->render('rss'), 200, ['Content-Type' => 'application/rss+xml']);
        } else {
            return $this->render('link/index.html.twig', [
                'entities' => $entities,
                'tag' => $tag,
                'categories' => $allCategories,
                'tags' => $allTags,
                'years' => $this->tagService->getYears(),
            ]);
        }
    }

    #[Route('/', name: 'tag_list', methods: ['GET'])]
    public function indexAction(): Response
    {
        $entities = $this->tagService->getAllTags();
        return $this->render('tag/index.html.twig', [
            'entities' => $entities,
        ]);
    }

    #[Route('/query/', methods: ['GET'], priority: 1, format: 'json')]
    public function queryAction(Request $request): Response
    {
        $accepts = AcceptHeader::fromString($request->headers->get('Accept'));
        if ($accepts->has('application/json')) {
            $entities = $this->tagService->searchTags($request->query->get('q'));
            $tags = array_map(fn($tag) => ['id' => $tag->getId(), 'name' => $tag->getName()], $entities);

            return new JsonResponse($tags);
        } else {
            return $this->redirectToRoute('tag_list');
        }
    }

    #[Route('/{slug}/delete', name: 'tag_delete', methods: ['GET'])]
    public function deleteAction(string $slug): Response
    {
        $entity = $this->tagService->getTagBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        if ($entity->getLinks()->count() > 0) {
            return $this->redirectToRoute('tag_list', ['haslinksname' => $entity->getName()]);
        }

        return $this->render('tag/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route('/{slug}/deleteconfirmed', name: 'tag_deleteconfirmed', methods: ['POST'])]
    public function deleteConfirmedAction(string $slug): Response
    {
        $entity = $this->tagService->getTagBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        if ($entity->isValid()) {
            $name = $entity->getName();
            $this->tagService->deleteTag($entity);

            return $this->redirectToRoute('tag_list', ['deletedname' => $name]);
        }

        return $this->render('tag/delete.html.twig', [
            'entity' => $entity,
        ]);
    }
}