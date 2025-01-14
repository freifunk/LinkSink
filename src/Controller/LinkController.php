<?php

namespace App\Controller;

use App\Service\LinkService;
use DateTime;
use App\Entity\Link;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/')]
class LinkController extends AbstractController
{

    private LinkService $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    #[Route("/", name: "index", methods: ["GET"])]
    public function indexAction(): Response
    {
        return $this->render('link/index.html.twig', [
            'entities' => $this->linkService->getLinks(),
            'categories' => $this->linkService->getAllCategories(),
            'tags' => $this->linkService->getAllTags(),
            'years' => $this->linkService->getYears(),
        ]);
    }

    #[Route("/filter", name: "_filter", methods: ["POST"])]
    public function filterAction(Request $request): Response
    {
        if ($request->get('category') && $request->get('tag') && $request->get('year')) {
            return $this->redirectToRoute('category_filter_year_tag', [
                'category' => $request->get('category'),
                'tag' => $request->get('tag'),
                'year' => $request->get('year'),
            ]);
        } elseif ($request->get('category') && $request->get('year')) {
            return $this->redirectToRoute('category_filter_year', [
                'category' => $request->get('category'),
                'year' => $request->get('year'),
            ]);
        } elseif ($request->get('category') && $request->get('tag')) {
            return $this->redirectToRoute('category_filter_tag', [
                'category' => $request->get('category'),
                'tag' => $request->get('tag'),
            ]);
        } elseif ($request->get('tag') && $request->get('year')) {
            return $this->redirectToRoute('year_tag_filter', [
                'year' => $request->get('year'),
                'tag' => $request->get('tag'),
            ]);
        } elseif ($request->get('category')) {
            return $this->redirectToRoute('category_filter', [
                'category' => $request->get('category'),
            ]);
        } elseif ($request->get('tag')) {
            return $this->redirectToRoute('tag_show', [
                'slug' => $request->get('tag'),
            ]);
        } elseif ($request->get('year')) {
            return $this->redirectToRoute('year_filter', [
                'year' => $request->get('year'),
            ]);
        } else {
            return $this->redirectToRoute('index');
        }
    }

    #[Route("/links/neu", name: "_new", methods: ["GET"])]
    public function newAction(Request $request): Response
    {
        $entity = new Link();

        if ($request->get('url') !== null) {
            $entity->setUrl($request->get('url'));
        }
        if ($request->get('date') !== null) {
            $pubdate = new DateTime("@" . $request->get('date'));
            $entity->setPubdate($pubdate);
        }
        if ($request->get('title') !== null) {
            $entity->setTitle($request->get('title'));
        }
        if ($request->get('description') !== null) {
            $entity->setDescription($request->get('description'));
        }

        return $this->render('link/new.html.twig', [
            'entity' => $entity,
            'categories' => $this->linkService->getAllCategories(),
            'tags' => $this->linkService->getAllTags(),
        ]);
    }

    #[Route("/links/", name: "_create", methods: ["POST"])]
    public function createAction(Request $request): Response
    {
        $entity = new Link();
        return $this->validateAndReturn($request, $entity);
    }

    #[Route("/links/{slug}", name: "_show", methods: ["GET"])]
    public function showAction(string $slug): Response
    {
        $entity = $this->linkService->getLinkBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        return $this->render('link/show.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route("/links/{slug}/edit", name: "_edit", methods: ["GET"])]
    public function editAction(string $slug): Response
    {
        $entity = $this->linkService->getLinkBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        return $this->render('link/edit.html.twig', [
            'entity' => $entity,
            'categories' => $this->linkService->getAllCategories(),
            'tags' => $this->linkService->getAllTags(),
        ]);
    }

    #[Route("/links/{slug}/delete", name: "_delete", methods: ["GET"])]
    public function deleteAction(string $slug): Response
    {
        $entity = $this->linkService->getLinkBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        return $this->render('link/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route("/links/{slug}/deleteconfirmed", name: "_deleteconfirmed", methods: ["POST"])]
    public function deleteConfirmedAction(string $slug): Response
    {
        $entity = $this->linkService->getLinkBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        if ($entity->isValid()) {
            $title = $entity->getTitle();
            $this->linkService->markLinkAsDeleted($entity);

            return $this->redirectToRoute('index', ['deletedtitle' => $title]);
        }

        return $this->render('link/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route("/links/{slug}", name: "_update", methods: ["POST"])]
    public function updateAction(Request $request, string $slug, LoggerInterface $logger): Response
    {
        $entity = $this->linkService->getLinkBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        $logger->warning('Update link ' . $entity->getId() . ' ' . $entity->getTitle());
        return $this->validateAndReturn($request, $entity);
    }

    /**
     * @param Request $request
     * @param Link $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function validateAndReturn(Request $request, Link $entity): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $category = $this->linkService->getCategoryBySlug($request->get('ls_category'));

        if ($entity->isValid() && !$request->get('ls_origin') && $category !== null) {
            $this->linkService->saveLink($request, $entity);
            return $this->redirectToRoute('_show', ['slug' => $entity->getSlug()]);
        } else {
            return $this->redirectToRoute('index');
        }
    }
}
