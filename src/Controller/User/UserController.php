<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Form\UserCredentialsType;
use Doctrine\ORM\EntityManagerInterface;
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
        if ($user->getEmail() === 'admin@admin.com') {
            $user->setRoles(['ROLE_ADMIN']);
            $em->persist($user);
            $em->flush();
        }

        if (!$user->getProfile()->getAvatar()) {
            $user->getProfile()->setAvatar(new Image());
        }

        $form = $this->createForm(ProfileType::class, $user->getProfile());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
}
