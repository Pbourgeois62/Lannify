<?php

namespace App\Controller\User;

use App\Entity\Event;
use App\Entity\Image;
use App\Form\EventType;
use App\Service\CodeGenerator;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/event')]
final class EventController extends AbstractController
{  
    #[Route('/{event}/home', name: 'event_home')]
    public function home(Event $event): Response
    {       
        return $this->render('event/home.html.twig', [
            'event' => $event
        ])
        ;
    }
    
    #[Route('/create', name: 'event_create')]
    public function create(Request $request, EntityManagerInterface $em, CodeGenerator $code): Response
    {
        $event = new Event();

        $event->setCoverImage(new Image());

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImage = $event->getCoverImage();
            if ($coverImage) {
                $coverImage->setEvent($event);
            }
            $event->setCode($code->generateEventCode($event));
            $event->setOrganizer($this->getUser());
            $event->addUser($this->getUser());

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');

            return $this->redirectToRoute('event_home', ['event' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }    

    #[Route('/join', name: 'event_join')]
    public function join(EventRepository $eventRepository, EntityManagerInterface $em, Request $request): Response
    {
        $code = $request->query->get('code');
        $event = $eventRepository->findOneBy(['code' => $code]);
        if (!$event) {
            $this->addFlash('error', 'Code d’événement invalide.');
            return $this->redirectToRoute('home');
        }
        $user = $this->getUser();
        if ($user && !$event->getUsers()->contains($user)) {
            $event->addUser($user);
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Vous avez rejoint la Lan avec succès !');
        } else {
            $this->addFlash('info', 'Vous participez déjà à cette Lan.');
        }
        return $this->redirectToRoute('event_home', [
            'event' => $event->getId(),
        ]);
    }

    #[Route('/{event}/edit', name: 'event_edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImage = $event->getCoverImage();
            if ($coverImage) {
                $coverImage->setEvent($event);
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement mis à jour avec succès !');

            return $this->redirectToRoute('event_home', ['event' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }


    #[Route('/{event}/delete', name: 'event_delete')]
    public function delete(Event $event, EntityManagerInterface $em): Response
    {
        $em->remove($event);
        $em->flush();
        $this->addFlash('success', 'Événement supprimé avec succès !');

        return $this->redirectToRoute('home');
    }

    #[Route('/{event}/close', name: 'event_close')]
    public function close(Event $event, EntityManagerInterface $em): Response
    {
        $event->setClosed(true);
        $em->flush();
        $this->addFlash('success', 'Événement cloturé avec succès !');

        return $this->redirectToRoute('home');
    }
}
