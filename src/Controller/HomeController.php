<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
final class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(#[CurrentUser] User $user, EventRepository $eventRepository): Response
    {
        $incomingEvents = $eventRepository->getUpcomingEventsForUser($user);        
        return $this->render('home/index.html.twig',[
            'incomingEvents' => $incomingEvents,            
        ]);
    }
}
