<?php

namespace App\Controller;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Géolocalisation")
 */
#[Route('/api')]
class GeoSearchController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository
    ) {}

    /**
     * Recherche les services à proximité
     * 
     * @OA\Get(
     *     path="/api/services/nearby",
     *     summary="Trouve les services à proximité d'un point",
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="number", format="float", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des services trouvés"
     *     )
     * )
     * @Security(name="Bearer")
     */
    #[Route('/services/nearby', name: 'api_services_nearby', methods: ['GET'])]
    public function getNearbyServices(Request $request): JsonResponse
    {
        $latitude = $request->query->get('latitude');
        $longitude = $request->query->get('longitude');
        $radius = $request->query->get('radius', 5.0);

        if (!$latitude || !$longitude) {
            return $this->json(['error' => 'Latitude et longitude requises'], 400);
        }

        $services = $this->serviceRepository->findNearby(
            (float) $latitude,
            (float) $longitude,
            (float) $radius
        );

        return $this->json([
            'center' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ],
            'radius' => $radius,
            'services' => $services
        ]);
    }

    /**
     * Recherche les services par adresse
     * 
     * @OA\Get(
     *     path="/api/services/search",
     *     summary="Trouve les services par adresse",
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="number", format="float", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des services trouvés"
     *     )
     * )
     * @Security(name="Bearer")
     */
    #[Route('/services/search', name: 'api_services_search', methods: ['GET'])]
    public function searchByAddress(Request $request): JsonResponse
    {
        $address = $request->query->get('address');
        $radius = $request->query->get('radius', 5.0);

        if (!$address) {
            return $this->json(['error' => 'Adresse requise'], 400);
        }

        // Coordonnées de test pour Paris
        $latitude = 48.8566;
        $longitude = 2.3522;

        $services = $this->serviceRepository->findNearby(
            $latitude,
            $longitude,
            (float) $radius
        );

        return $this->json([
            'address' => $address,
            'coordinates' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ],
            'radius' => $radius,
            'services' => $services
        ]);
    }
}