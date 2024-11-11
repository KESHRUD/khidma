<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[Route('/api')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
        private readonly SerializerInterface $serializer
    ) {}

    #[Route('/categories/active', name: 'api_get_active_categories', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne toutes les catégories actives',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Category::class, groups: ['category:read']))
        )
    )]
    #[OA\Tag(name: 'Category')]
    public function getActiveCategories(): JsonResponse
    {
        $categories = $this->categoryRepository->findBy(['isActive' => true]);
        
        $jsonCategories = $this->serializer->serialize(
            $categories,
            'json',
            ['groups' => 'category:read']
        );
        
        return new JsonResponse($jsonCategories, Response::HTTP_OK, [], true);
    }

    #[Route('/categories/{id}/toggle', name: 'api_toggle_category', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Active/désactive une catégorie',
        content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:read']))
    )]
    #[OA\Tag(name: 'Category')]
    public function toggleCategory(Category $category): JsonResponse
    {
        $category->setIsActive(!$category->isActive());
        $this->entityManager->flush();
        
        return new JsonResponse(
            $this->serializer->serialize($category, 'json', ['groups' => 'category:read']),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/categories/search', name: 'api_search_categories', methods: ['GET'])]
    #[OA\Parameter(
        name: 'q',
        in: 'query',
        description: 'Terme de recherche',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Recherche des catégories',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Category::class, groups: ['category:read']))
        )
    )]
    #[OA\Tag(name: 'Category')]
    public function searchCategories(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        $categories = $this->categoryRepository->createQueryBuilder('c')
            ->where('c.name LIKE :query OR c.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
            
        return new JsonResponse(
            $this->serializer->serialize($categories, 'json', ['groups' => 'category:read']),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/categories/stats', name: 'api_category_stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Statistiques des catégories',
        content: new OA\JsonContent(type: 'object')
    )]
    #[OA\Tag(name: 'Category')]
    public function getCategoryStats(): JsonResponse
    {
        $stats = $this->categoryRepository->createQueryBuilder('c')
            ->select('COUNT(c.id) as total')
            ->addSelect('SUM(CASE WHEN c.isActive = true THEN 1 ELSE 0 END) as active')
            ->addSelect('COUNT(s.id) as total_services')
            ->leftJoin('c.services', 's')
            ->getQuery()
            ->getSingleResult();

        return new JsonResponse($stats);
    }

    #[Route('/categories/bulk-toggle', name: 'api_bulk_toggle_categories', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        description: 'Liste des IDs des catégories à basculer',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'integer')),
                new OA\Property(property: 'active', type: 'boolean')
            ]
        )
    )]
    #[OA\Tag(name: 'Category')]
    public function bulkToggleCategories(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];
        $active = $data['active'] ?? true;

        if (empty($ids)) {
            return new JsonResponse(['error' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $categories = $this->categoryRepository->findBy(['id' => $ids]);
        foreach ($categories as $category) {
            $category->setIsActive($active);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => count($categories) . ' categories updated',
            'updated_ids' => $ids
        ]);
    }
}