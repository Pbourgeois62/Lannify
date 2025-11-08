<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\FeedbackRepository;
use App\Repository\GameSessionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
final class DashboardController extends AbstractController
{
    #[Route('/home', name: 'admin_home')]
    public function index(
        UserRepository $userRepository,
        EventRepository $eventRepository,
        FeedbackRepository $feedbackRepository,
        GameSessionRepository $gameSessionRepository
    ): Response
    {
        $stats = [
            'users' => $userRepository->count([]),            
            'feedbacks' => $feedbackRepository->count([]),
            'events' => $eventRepository->count([]),
            'gameSessions' => $gameSessionRepository->count([]),
        ];

        $latestEvents = $eventRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $latestFeedbacks = $feedbackRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,           
            'latestEvents' => $latestEvents,
            'latestFeedbacks' => $latestFeedbacks,
        ]);
    }
}
