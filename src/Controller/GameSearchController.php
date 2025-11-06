<?php

namespace App\Controller;

use App\Service\RawgClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GameSearchController extends AbstractController
{
    // #[Route('/api/games/search', name: 'api_games_search', methods: ['GET'])]
    // public function searchGames(Request $request): JsonResponse
    // {
    //     $query = $request->query->get('q', '');
    //     if (!$query) {
    //         return new JsonResponse([], 200);
    //     }

    //     $apiKey = $_ENV['RAWG_API_KEY'];
    //     $url = "https://api.rawg.io/api/games?key={$apiKey}&search=" . urlencode($query);

    //     $response = file_get_contents($url);
    //     $data = json_decode($response, true);

    //     $results = array_map(fn($game) => [
    //         'id' => $game['id'],
    //         'name' => $game['name'],
    //         'released' => $game['released'] ?? '',
    //         'image' => $game['background_image'] ?? '',
    //         'genres' => array_map(fn($g) => $g['name'], $game['genres'] ?? []),
    //         'platforms' => array_map(fn($p) => $p['platform']['name'], $game['platforms'] ?? []),
    //     ], $data['results'] ?? []);

    //     return new JsonResponse($results);
    // }
    #[Route('/games-search', name: 'rawg_search')]
    public function search(Request $request, RawgClient $rawg): Response
    {
        $q = $request->query->get('q', 'doom');
        $data = $rawg->searchGames($q, 1, 24);

        return $this->render('tests/search.html.twig', [
            'query' => $q,
            'results' => $data['results'] ?? [],
            'count' => $data['count'] ?? 0,
        ]);
    }    
}
