<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\Tag;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eko\FeedBundle\Feed\FeedManager;
use Eko\FeedBundle\Field\Item\ItemField;
use Eko\FeedBundle\Field\Item\MediaItemField;


use Symfony\{Bundle\FrameworkBundle\Controller\AbstractController,
    Component\HttpFoundation\JsonResponse,
    Component\HttpFoundation\Request,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Response,
    Component\HttpFoundation\AcceptHeader};

#[Route('/tags')]
class TagController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TagRepository $tagRepository;
    private CategoryRepository $categoryRepository;
    private FeedManager $feedManager;

    public function __construct(EntityManagerInterface $entityManager, TagRepository $tagRepository, CategoryRepository $categoryRepository, FeedManager $feedManager)
    {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->feedManager = $feedManager;
    }

    #[Route('/{slug}.{format}', name: 'tag_show', defaults: ['format' => 'html'], methods: ['GET'])]
    public function showAction(string $slug, string $format)
    {
        $allCategories = $this->categoryRepository->findAll();

        $allTags = $this->tagRepository->findBy([], ['slug' => 'ASC']);

        $tag = $this->tagRepository->findOneBy(['slug' => $slug]);

        if (!$tag) {
            throw $this->createNotFoundException('Unable to find tag entity.');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.pubyear')
            ->from(Link::class, 'e')
            ->orderBy('e.pubyear', 'desc')
            ->groupBy('e.pubyear');
        $years = $qb->getQuery()->execute();
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('e')
            ->from(Link::class, 'e')
            ->join('e.tags', 't', 'WITH', $qb->expr()->in('t.id', $tag->getId()))
            ->where('e.deleted IS NULL')
            ->orderBy('e.pubdate', 'desc');
        $entities = $qb->getQuery()->execute();

        if ($format == 'rss') {
            $feed = $this->feedManager->get('news');
            $feed->addItemField(new ItemField('category', 'getCategoryName'));
            $feed->addItemField(new MediaItemField('getFeedMediaItem'));
            $feed->addFromArray($entities);

            return new Response($feed->render('rss'), 200, ['Content-Type' => 'application/rss+xml']);
        } else {
            return $this->render('link/index.html.twig', array(
                'entities' => $entities,
                'tag' => $tag,
                'categories' => $allCategories,
                'tags' => $allTags,
                'years' => $years,
            ));
        }
    }

    #[Route('/', name: 'tag_list', methods: ['GET'])]
    public function indexAction(): Response
    {
        $repo = $this->entityManager->getRepository(Tag::class);
        $entities = $repo->findAll();
        return $this->render('tag/index.html.twig', [
            'entities' => $entities,
        ]);
    }

    #[Route('/query/', methods: ['GET'], priority: 1, format: 'json')]
    public function queryAction(Request $request): Response
    {
        $accepts = AcceptHeader::fromString($request->headers->get('Accept'));
        if ($accepts->has('application/json')) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('t')
                ->from(Tag::class, 't')
                ->where('t.name LIKE :tag')
                ->orderBy('t.name')
                ->setParameter('tag', sprintf('%%%s%%', strtolower($request->query->get('q'))));
            $entities = $qb->getQuery()->getResult();

            $tags = array_map(fn($tag) => ['id' => $tag->getId(), 'name' => $tag->getName()], $entities);

            return new JsonResponse($tags);
        } else {
            return $this->redirectToRoute('tag_list');
        }
    }

    #[Route('/{slug}/delete', name: 'tag_delete', methods: ['GET'])]
    public function deleteAction(string $slug): Response
    {
        $entity = $this->tagRepository->findOneBy(['slug' => $slug]);

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
    public function deleteConfirmedAction(Request $request, string $slug): Response
    {
        $entity = $this->tagRepository->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        if ($entity->isValid()) {
            $name = $entity->getName();
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            return $this->redirectToRoute('tag_list', ['deletedname' => $name]);
        }

        return $this->render('tag/delete.html.twig', [
            'entity' => $entity,
        ]);
    }
}
