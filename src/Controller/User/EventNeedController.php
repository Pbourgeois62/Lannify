<?php

namespace App\Controller\User;

use App\Entity\Need;
use App\Entity\User;
use App\Entity\Event;
use App\Form\NeedType;
use App\Entity\NeedContribution;
use App\Form\NeedContributionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NeedContributionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('event/{event}/')]
final class EventNeedController extends AbstractController
{
    #[Route('needs', name: 'event_needs', methods: ['GET'])]
    public function showNeeds(
        Event $event,
        #[CurrentUser] ?User $user,
        NeedContributionRepository $needContributionRepository
    ): Response {
        // dump($this->getUser());
        $forms = [];

        foreach ($event->getNeeds() as $need) {
            $contribution = $needContributionRepository->findOneBy([
                'need' => $need,
                'user' => $user,
            ]) ?? (new NeedContribution())->setNeed($need)->setUser($user);

            $form = $this->createForm(NeedContributionType::class, $contribution, [
                'action' => $this->generateUrl('need_contribute', [
                    'event' => $event->getId(),
                    'id'    => $need->getId(),
                ]),
                'method' => 'POST',
                'remaining_quantity' => $need->getRemainingQuantityNotFromUser($user),
            ]);

            $forms[$need->getId()] = $form->createView();
        }

        return $this->render('event/needs/index.html.twig', [
            'event' => $event,
            'forms' => $forms,
        ]);
    }

    #[Route('need/create', name: 'need_create', methods: ['GET', 'POST'])]
    public function new(
        #[CurrentUser] ?User $user,
        Event $event,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $need = new Need();
        $form = $this->createForm(NeedType::class, $need);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $need->setEvent($event);
            $need->setCreatedBy($user);
            $em->persist($need);
            $em->flush();

            $this->addFlash('success', 'Le besoin a été ajouté avec succès.');

            return $this->redirectToRoute('event_needs', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('event/needs/form.html.twig', [
            'event' => $event,
            'form'  => $form,
            'isEdit' => false,
        ]);
    }

    #[Route('need/{need}/edit', name: 'need_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Event $event,
        Need $need,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if ($need->getCreatedBy() !== $user && $event->getOrganizer() !== $user) {
            $this->addFlash('error', 'Vous n’avez pas les droits pour modifier ce besoin.');
            return $this->redirectToRoute('event_home', [
                'id' => $event->getId(),                
            ]);
        }

        $form = $this->createForm(NeedType::class, $need);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Besoin modifié avec succès');

            return $this->redirectToRoute('event_needs', [
                'event' => $event->getId(),                
            ]);
        }

        return $this->render('event/needs/form.html.twig', [
            'form'  => $form,
            'event' => $event,
            'isEdit' => true,            
        ]);
    }

    #[Route('need/{need}/delete', name: 'need_delete', methods: ['POST', 'GET'])]
    public function delete(
        Request $request,
        Event $event,
        Need $need,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if ($need->getCreatedBy() !== $user && $event->getOrganizer() !== $user) {
            $this->addFlash('error', 'Vous n’avez pas les droits pour supprimer ce besoin.');
            return $this->redirectToRoute('event_home', [
                'id' => $event->getId(),                
            ]);
        }

        if ($this->isCsrfTokenValid('delete_need_' . $need->getId(), $request->get('_token'))
            || $request->isMethod('GET')
        ) {
            $em->remove($need);
            $em->flush();
            $this->addFlash('success', 'Besoin supprimé avec succès');
        }

        return $this->redirectToRoute('event_needs', [
            'event' => $event->getId(),            
        ]);
    }

    #[Route('need/{id}/contribute', name: 'need_contribute', methods: ['POST'])]
    public function contribute(
        #[CurrentUser] ?User $user,
        Request $request,
        Event $event,
        Need $need,
        EntityManagerInterface $em
    ): Response {
        $contribution = $em->getRepository(NeedContribution::class)->findOneBy([
            'need' => $need,
            'user' => $user,
        ]) ?? (new NeedContribution())->setNeed($need)->setUser($user);

        $form = $this->createForm(NeedContributionType::class, $contribution, [
            'remaining_quantity' => $need->getRemainingQuantityNotFromUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($contribution);
            $em->flush();
            $this->addFlash('success', 'Contribution enregistrée');
        } else {
            $this->addFlash('error', 'Erreur lors de la contribution.');
        }

        return $this->redirectToRoute('event_needs', [
            'event' => $event->getId(),            
        ]);
    }
}
