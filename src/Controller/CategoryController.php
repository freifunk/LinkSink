<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/kategorie')]
class CategoryController extends AbstractController
{

    private CategoryService $categoryService;
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryService $categoryService, CategoryRepository $categoryRepository)
    {
        $this->categoryService = $categoryService;
        $this->categoryRepository = $categoryRepository;
    }
    #[Route('/s/{category}.{format}', name: 'old_category_filter', defaults: ['year' => '', 'tag' => '', 'format' => 'html'], methods: ['GET'])]
    #[Route('/s/{category}/{year}.{format}', name: 'old_category_filter_year', requirements: ['year' => '\d{4}'], defaults: ['tag' => '', 'format' => 'html'], methods: ['GET'])]
    #[Route('/s/{category}/{year}/{tag}.{format}', name: 'old_category_filter_year_tag', requirements: ['year' => '\d{4}'], defaults: ['format' => 'html'], methods: ['GET'])]
    #[Route('/s/{category}/{tag}.{format}', name: 'old_category_filter_tag', requirements: ['tag' => '[A-Za-z0-9\-]+'], defaults: ['year' => '', 'format' => 'html'], methods: ['GET'])]
    public function showAction(string $category, string $year, string $tag, string $format): Response
    {
        $myRoute = $this->container->get('request_stack')->getCurrentRequest()->attributes->get('_route');
        $routeParams = ['category' => $category, 'format' => $format];

        if ($myRoute === 'old_category_filter_year') {
            $routeParams['year'] = $year;
        } elseif ($myRoute === 'old_category_filter_year_tag') {
            $routeParams['year'] = $year;
            $routeParams['tag'] = $tag;
        } elseif ($myRoute === 'old_category_filter_tag') {
            $routeParams['tag'] = $tag;
        }

        if (str_starts_with($myRoute, 'old_')) {
            return $this->redirectToRoute(str_replace('old_', '', $myRoute), $routeParams, 301);
        }

        return $this->redirect('/');
    }

    #[Route('/', name: 'category_show', methods: ['GET'])]
    public function indexAction(): Response
    {
        $entities = $this->categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'entities' => $entities,
        ]);
    }


    #[Route('/{slug}/bearbeiten', name: 'category_edit', methods: ['GET'])]
    public function editAction(string $slug): Response
    {
        $entity = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        return $this->render('category/edit.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route('/{slug}/update', name: 'category_update', methods: ['POST'])]
    public function updateAction(Request $request, string $slug): Response
    {
        $entity = $this->categoryService->updateCategory($slug, $request->get('name'));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        if ($entity->isValid()) {
            return $this->redirectToRoute('category_show');
        }

        return $this->render('category/edit.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route('/create', name: 'category_create', methods: ['POST'])]
    public function createAction(Request $request): Response
    {
        $name = filter_var($request->get('name'), FILTER_SANITIZE_SPECIAL_CHARS);
        $entity = $this->categoryService->createCategory($name);

        if ($entity->isValid()) {
            return $this->redirectToRoute('category_show');
        }

        return $this->render('category/new.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route('/neu', name: 'category_new', methods: ['GET'])]
    public function newAction(): Response
    {
        $category = new Category();

        return $this->render('category/new.html.twig', [
            'entity' => $category,
        ]);
    }


    #[Route('/{slug}/delete', name: 'category_delete', methods: ['GET'])]
    public function deleteAction(string $slug): Response
    {
        $entity = $this->categoryRepository->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        if ($entity->getLinks()->count() > 0) {
            return $this->redirectToRoute('category_show', ['haslinksname' => $entity->getName()]);
        }

        return $this->render('category/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route('/{slug}/deleteconfirmed', name: 'category_deleteconfirmed', methods: ['POST'])]
    public function deleteConfirmedAction(string $slug): Response
    {
        $entity = $this->categoryService->deleteCategory($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        if ($entity->isValid()) {
            $name = $entity->getName();
            return $this->redirectToRoute('category_show', ['deletedname' => $name]);
        }

        return $this->render('category/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

}
