<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\EventImage;
use App\Form\EventImageType;
use App\Repository\EventImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/event/{event}', name: 'event_photo_')]
final class EventPhotoController extends AbstractController
{
    #[Route('/photos/index', name: 'index', methods: ['GET'])]
    public function index(
        #[CurrentUser] ?User $user,
        Event $event,
        EventImageRepository $eventImageRepository
    ): Response {
        $organizer = $event->getOrganizer();
        $photos = ($user && $organizer && $user->getId() === $organizer->getId())
            ? $event->getEventImages()
            : $eventImageRepository->getAllApprovedImagesByEventId($event->getId());

        return $this->render('event/photos/index.html.twig', [
            'photos' => $photos,
            'event' => $event,
        ]);
    }

    #[Route('/photo/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(
        #[CurrentUser] User $user,
        Event $event,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $eventImage = new EventImage();
        $form = $this->createForm(EventImageType::class, $eventImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventImage->setEvent($event);
            $eventImage->setUploadedBy($user);

            $em->persist($eventImage);
            $em->flush();

            $this->addFlash('success', 'Photo ajoutée avec succès. Elle sera visible une fois validée par l’organisateur.');
            return $this->redirectToRoute('event_photo_index', ['event' => $event->getId()]);
        }

        return $this->render('event/photos/form.html.twig', [
            'form' => $form,
            'event' => $event,
        ]);
    }

    #[Route('/photo/{photo}/delete', name: 'delete', methods: ['POST', 'GET'])]
    public function delete(
        EventImage $photo,
        EntityManagerInterface $em
    ): Response {
        $event = $photo->getEvent();

        $em->remove($photo);
        $em->flush();

        $this->addFlash('success', 'Photo supprimée avec succès.');
        return $this->redirectToRoute('event_photo_index', ['event' => $event->getId()]);
    }

    #[Route('/photo/{photo}/approve', name: 'approve', methods: ['POST', 'GET'])]
    public function approve(
        EventImage $photo,
        #[CurrentUser] User $user,
        EntityManagerInterface $em
    ): Response {
        $event = $photo->getEvent();

        if (!$user || $user !== $event->getOrganizer()) {
            throw $this->createAccessDeniedException('Action non autorisée.');
        }

        $photo->setIsApproved(true);
        $em->flush();

        $this->addFlash('success', 'Photo validée.');
        return $this->redirectToRoute('event_photo_index', ['event' => $event->getId()]);
    }
}
