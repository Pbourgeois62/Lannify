<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

class RawgClient
{
    private HttpClientInterface $http;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $http, LoggerInterface $logger, string $rawgApiKey)
    {
        $this->http = $http;
        $this->logger = $logger;
        $this->apiKey = $rawgApiKey;
    }

    public function searchGames(string $query, int $page = 1, int $pageSize = 20): array
{
    if (trim($query) === '') {
        return [];
    }

    try {
        $response = $this->http->request('GET', 'https://api.rawg.io/api/games', [
            'query' => [
                'key' => $this->apiKey,
                'search' => $query,
                'page' => $page,
                'page_size' => $pageSize,
            ],
        ]);

        return $response->toArray();
    } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
        $this->logger->error('RAWG API search error', [
            'message' => $e->getMessage(),
            'query' => $query,
        ]);
        return ['error' => 'Erreur de connexion à l’API RAWG.'];
    } catch (\Throwable $e) {
        $this->logger->error('Unexpected RAWG error', ['exception' => $e]);
        return ['error' => 'Erreur inattendue.'];
    }
}


    public function getGame(int $id): ?array
    {
        try {
            $response = $this->http->request('GET', "https://api.rawg.io/api/games/{$id}", [
                'query' => [
                    'key' => $this->apiKey,
                ],
            ]);

            return $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            $this->logger->error('RAWG API getGame error', [
                'message' => $e->getMessage(),
                'game_id' => $id,
            ]);
            return null;
        } catch (\Throwable $e) {
            $this->logger->error('Unexpected RAWG getGame error', ['exception' => $e]);
            return null;
        }
    }
}
