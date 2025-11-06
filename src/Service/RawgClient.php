<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RawgClient
{
    private HttpClientInterface $http;
    private string $apiKey;

    public function __construct(HttpClientInterface $http, string $rawgApiKey)
    {
        $this->http = $http;
        $this->apiKey = $rawgApiKey;
    }

    /**
     * Recherche de jeux RAWG
     */
    public function searchGames(string $query, int $page = 1, int $pageSize = 20): array
    {
        $response = $this->http->request('GET', 'https://api.rawg.io/api/games', [
            'query' => [
                'key' => $this->apiKey,
                'search' => $query,
                'page' => $page,
                'page_size' => $pageSize,
            ],
        ]);

        return $response->toArray();
    }

    /**
     * Récupère un jeu par son id
     */
    public function getGame(int $id): ?array
    {
        $response = $this->http->request('GET', "https://api.rawg.io/api/games/{$id}", [
            'query' => [
                'key' => $this->apiKey,
            ],
        ]);

        return $response->toArray();
    }
}
