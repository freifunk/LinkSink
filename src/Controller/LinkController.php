<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use App\Entity\Enclosure;
use App\Entity\Link;
use App\Entity\Tag;
use Eko\FeedBundle\Feed\FeedManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/')]
class LinkController extends AbstractController
{

    protected FeedManager $feedManager;
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;


    public function __construct(FeedManager $feedManager, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->feedManager = $feedManager;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    #[Route("/", name: "index", methods: ["GET"])]
    public function indexAction(): Response
    {
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $allCategories = $categoryRepository->findAll();

        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $allTags = $tagRepository->findAllOrderedBySlug();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.pubyear')
            ->from(Link::class, 'e')
            ->orderBy('e.pubyear', 'desc')
            ->groupBy('e.pubyear');
        $years = $qb->getQuery()->getResult();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e')
            ->from(Link::class, 'e')
            ->where('e.deleted IS NULL')
            ->orderBy('e.pubdate', 'desc');
        $entities = $qb->getQuery()->getResult();

        return $this->render('link/index.html.twig', [
            'entities' => $entities,
            'categories' => $allCategories,
            'tags' => $allTags,
            'years' => $years,
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
            $pubdate = new \DateTime("@" . $request->get('date'));
            $entity->setPubdate($pubdate);
        }
        if ($request->get('title') !== null) {
            $entity->setTitle($request->get('title'));
        }
        if ($request->get('description') !== null) {
            $entity->setDescription($request->get('description'));
        }

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $allTags = $tagRepository->findAllOrderedBySlug();

        return $this->render('link/new.html.twig', [
            'entity' => $entity,
            'categories' => $categories,
            'tags' => $allTags,
        ]);
    }

    #[Route("/links/", name: "_create", methods: ["POST"])]
    public function createAction(Request $request): Response
    {
        $entity = new Link();

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['slug' => $request->get('ls_category')]);

        if ($entity->isValid() && !$request->get('ls_origin') && $category !== null) {
            $this->saveLink($request, $entity);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return $this->redirectToRoute('_show', ['slug' => $entity->getSlug()]);
        } else {
            return $this->redirectToRoute('index');
        }
    }

    #[Route("/links/{slug}", name: "_show", methods: ["GET"])]
    public function showAction(string $slug): Response
    {
        $repo = $this->entityManager->getRepository(Link::class);
        $entity = $repo->findOneBy(['slug' => $slug]);

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
        $repo = $this->entityManager->getRepository(Link::class);
        $entity = $repo->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $allTags = $tagRepository->findAllOrderedBySlug();

        return $this->render('link/edit.html.twig', [
            'entity' => $entity,
            'categories' => $categories,
            'tags' => $allTags,
        ]);
    }

    #[Route("/links/{slug}/delete", name: "_delete", methods: ["GET"])]
    public function deleteAction(string $slug): Response
    {
        $repo = $this->entityManager->getRepository(Link::class);
        $entity = $repo->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        return $this->render('link/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route("/links/{slug}/deleteconfirmed", name: "_deleteconfirmed", methods: ["POST"])]
    public function deleteConfirmedAction(Request $request, string $slug): Response
    {
        $repo = $this->entityManager->getRepository(Link::class);
        $entity = $repo->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        if ($entity->isValid()) {
            $title = $entity->getTitle();
            $entity->setDeleted(true);
            $entity->setDeletedAt(new \DateTime());
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return $this->redirectToRoute('index', ['deletedtitle' => $title]);
        }

        return $this->render('link/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route("/links/{slug}", name: "_update", methods: ["POST"])]
    public function updateAction(Request $request, string $slug, LoggerInterface $logger): Response
    {
        $repo = $this->entityManager->getRepository(Link::class);
        $entity = $repo->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Link entity.');
        }

        $logger->warning('Update link ' . $entity->getId() . ' ' . $entity->getTitle());
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['slug' => $request->get('ls_category')]);

        if ($entity->isValid() && !$request->get('ls_origin') && $category !== null) {
            $this->saveLink($request, $entity);
            return $this->redirectToRoute('_show', ['slug' => $entity->getSlug()]);
        } else {
            return $this->redirectToRoute('index');
        }
    }

    public function saveLink(Request $request, Link $entity): void
    {
        $pubdate = new \DateTime($request->get('ls_pubdate'));
        $entity->setPubdate($pubdate);
        $entity->setPubyear($pubdate->format("Y"));
        $entity->setGuid(filter_var($request->get('ls_url'), FILTER_SANITIZE_URL));
        $entity->setDescription(htmlspecialchars($request->get('ls_description'), ENT_QUOTES, 'UTF-8'));
        $entity->setTitle(filter_var($request->get('ls_title'), FILTER_SANITIZE_SPECIAL_CHARS));
        $entity->setUrl(filter_var($request->get('ls_url'), FILTER_SANITIZE_URL));

        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['slug' => $request->get('ls_category')]);
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
