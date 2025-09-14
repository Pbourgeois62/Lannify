<?php

namespace App\Controller;

use App\Entity\Need;
use App\Entity\User;
use App\Entity\Event;
use App\Form\NeedType;
use App\Entity\NeedContribution;
use App\Form\NeedContributionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/need')]
final class NeedController extends AbstractController
{
    #[Route('/{id}/create', name: 'need_create', methods: ['GET', 'POST'])]
    public function new(#[CurrentUser] ?User $user, Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $need  = new Need();
        $form = $this->createForm(NeedType::class, $need);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $need->setEvent($event);
            $need->setCreatedBy($user);
            $em->persist($need);
            $em->flush();
            $this->addFlash('success', 'Le besoin a été ajouté avec succès.');
            return $this->redirectToRoute('event_infos', ['id' => $event->getId(), '_fragment' => 'needs']);
        }
        return $this->render('need/form.html.twig', [
            'event' => $event,
            'form' => $form,
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'need_edit')]
    public function edit(Request $request, Need $need, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $event = $need->getEvent();

        if ($need->getCreatedBy() !== $user && $event->getOrganizer() !== $user) {
            $this->addFlash('error', 'Vous n’avez pas les droits pour modifier ce besoin.');
            return $this->redirectToRoute('event_infos', ['id' => $event->getId(), '_fragment' => 'needs']);
        }

        $form = $this->createForm(NeedType::class, $need);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Besoin modifié avec succès ✅');

            return $this->redirectToRoute('event_needs', ['id' => $event->getId(), '_fragment' => 'needs']);
        }

        return $this->render('need/form.html.twig', [
            'form' => $form,
            'event' => $need->getEvent(),
            'isEdit' => true,
            '_fragment' => 'needs'
        ]);
    }

    #[Route('/{id}/delete', name: 'need_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, Need $need, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $event = $need->getEvent();

        // Vérification des droits
        if ($need->getCreatedBy() !== $user && $event->getOrganizer() !== $user) {
            $this->addFlash('error', 'Vous n’avez pas les droits pour supprimer ce besoin.');
            return $this->redirectToRoute('event_infos', ['id' => $event->getId(), '_fragment' => 'needs']);
        }

        if ($this->isCsrfTokenValid('delete_need_' . $need->getId(), $request->get('_token')) || $request->isMethod('GET')) {
            $em->remove($need);
            $em->flush();

            $this->addFlash('success', 'Besoin supprimé avec succès 🗑');
        }

        return $this->redirectToRoute('event_infos', ['id' => $event->getId(), '_fragment' => 'needs']);
    }

    #[Route('/need/{id}/contribute', name: 'need_contribute', methods: ['POST'])]
    public function contribute(#[CurrentUser] ?User $user, Request $request, Need $need, EntityManagerInterface $em): Response
    {       
        $contribution = $em->getRepository(NeedContribution::class)->findOneBy([
            'need' => $need,
            'user' => $user,
        ]) ?? (new NeedContribution())->setNeed($need)->setUser($user);
        $remainingQuantity = $need->getRemainingQuantityNotFromUser($user);
        // dd($remainingQuantity);
        $form = $this->createForm(NeedContributionType::class, $contribution, [
            'remaining_quantity' => $remainingQuantity,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($contribution);
            $em->flush();
            $this->addFlash('success', 'Contribution enregistrée ✅');
        } else {
            $this->addFlash('error', 'Erreur lors de la contribution.');
        }

        return $this->redirectToRoute('event_needs', [
            'id' => $need->getEvent()->getId(),
            '_fragment' => 'needs',
        ]);
    }
}
