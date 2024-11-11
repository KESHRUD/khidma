<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Psr\Log\LoggerInterface;

class AddressAutocompleteService
{
    private const NOMINATIM_BASE_URL = 'https://nominatim.openstreetmap.org';
    private $cache;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {
        $this->cache = new FilesystemAdapter();
    }

    public function autocomplete(string $query): array
    {
        $cacheKey = 'address_' . md5($query);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        try {
            $response = $this->client->request('GET', self::NOMINATIM_BASE_URL . '/search', [
                'query' => [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 5,
                    'addressdetails' => 1
                ],
                'headers' => [
                    'User-Agent' => 'KhidmaApp/1.0',
                    'Accept' => 'application/json'
                ]
            ]);

            $results = array_map(function($item) {
                return [
                    'display_name' => $item['display_name'],
                    'latitude' => $item['lat'],
                    'longitude' => $item['lon'],
                    'address' => [
                        'street' => $item['address']['road'] ?? null,
                        'city' => $item['address']['city'] ?? $item['address']['town'] ?? null,
                        'state' => $item['address']['state'] ?? null,
                        'postcode' => $item['address']['postcode'] ?? null,
                        'country' => $item['address']['country'] ?? null
                    ]
                ];
            }, $response->toArray());

            $cacheItem->set($results);
            $cacheItem->expiresAfter(3600); // Cache pour 1 heure
            $this->cache->save($cacheItem);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Erreur autocomplétion: ' . $e->getMessage());
            return [];
        }
    }
}