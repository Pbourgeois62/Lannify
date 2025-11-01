<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users')]
final class UserManagementController extends AbstractController
{
    #[Route('/', name: 'admin_user_management')]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findall(SORT_DESC);
        return $this->render('admin/users/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/delete/{user}', name: 'admin_user_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('admin_user_management');
    }
}
