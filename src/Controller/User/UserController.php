<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Form\UserCredentialsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_USER')]
#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile')]
    public function profile(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $em): Response
    {
        $profile = $user->getProfile();

        $originalNickname  = $profile->getNickname();

        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);        

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedNickname = $form->get('nickname')->getData();

            if ($submittedNickname !== $originalNickname) {
                $profile->setNickNameEdited(true);
            } else {
                $profile->setNickNameEdited(false);
            }
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('home');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/credentials', name: 'user_edit_credentials')]
    public function editCredentials(#[CurrentUser] User $user, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserCredentialsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if ($password) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }

            $em->flush();
            $this->addFlash('success', 'Identifiants mis à jour !');

            return $this->redirectToRoute('home');
        }

        return $this->render('user/edit_credentials.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/delete', name: 'user_delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete-account', $token)) {
            $this->addFlash('error', 'Token invalide, suppression annulée.');
            return $this->redirectToRoute('user_profile');
        }

        // 1️⃣ Invalider la session
        $request->getSession()->invalidate();

        // 2️⃣ Supprimer l’utilisateur
        $em->remove($user);
        $em->flush();

        // 3️⃣ Rediriger vers la page login
        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        return $this->redirectToRoute('app_login');
    }
}
