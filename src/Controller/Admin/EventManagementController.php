<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/events')]
final class EventManagementController extends AbstractController
{
    #[Route('/', name: 'admin_events_management')]
    public function list(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findall(SORT_DESC);
        return $this->render('admin/events/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/delete/{event}', name: 'admin_event_delete', methods: ['POST', 'GET'])]
    public function delete(Event $event, EntityManagerInterface $em): Response
    {
        $em->remove($event, true);
        $em->flush();

        $this->addFlash('success', 'Evénement supprimé avec succès.');

        return $this->redirectToRoute('admin_events_management');
    }

    #[Route('/edit-magicToken/{event}', name: 'admin_event_edit_magicToken', methods: ['POST', 'GET'])]
    public function editMagicLink(Event $event, EntityManagerInterface $em, TokenGenerator $TokenGenerator): Response
    {        
        $event->setMagicToken($TokenGenerator->generateMagicLinkToken('lan'));      
        
        $em->flush();

        $this->addFlash('success', 'Le lien magique a été régénéré avec succès.');

        return $this->redirectToRoute('admin_events_management');
    }
}
