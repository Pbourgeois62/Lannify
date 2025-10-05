<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
final class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(#[CurrentUser] User $user, EventRepository $eventRepository, Request $request): Response
    {
        $session = $request->getSession();
        $showFeedback = !$session->get('feedback_seen', false);
        $openedEvents = $eventRepository->getOpenedEventsForUser($user);
        $closedEvents = $eventRepository->getClosedEventsForUser($user);
        return $this->render('home/index.html.twig',[
            'openedEvents' => $openedEvents,
            'closedEvents' => $closedEvents,
            'showFeedback' => $showFeedback,
        ]);
    }
}
