<?php

namespace App\Controller;

use App\Service\AddressAutocompleteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
class AddressController extends AbstractController
{
    public function __construct(
        private readonly AddressAutocompleteService $autocompleteService
    ) {}

    #[Route('/addresses/autocomplete', name: 'api_address_autocomplete', methods: ['GET'])]
    #[OA\Parameter(
        name: 'q',
        in: 'query',
        description: 'Terme de recherche',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Retourne les suggestions d\'adresses',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'display_name', type: 'string'),
                    new OA\Property(property: 'latitude', type: 'number'),
                    new OA\Property(property: 'longitude', type: 'number')
                ]
            )
        )
    )]
    #[OA\Tag(name: 'Address')]
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query->get('q');
        if (empty($query)) {
            return $this->json(['error' => 'Query parameter is required'], 400);
        }

        $suggestions = $this->autocompleteService->autocomplete($query);
        return $this->json($suggestions);
    }
}