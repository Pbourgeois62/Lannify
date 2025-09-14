<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\Profile;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile')]
    public function profile(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $em): Response
    {

        if (!$user->getProfile()) {
            $profile = new Profile();
            $user->setProfile($profile);
        } else {
            $profile = $user->getProfile();
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
}
