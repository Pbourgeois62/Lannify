<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
final class HomeController extends AbstractController
{
    #[Route('/dash/board', name: 'admin_home')]
    public function index(UserRepository $userRepository): Response
    {
        $allUsers = $userRepository->findAll();
        return $this->render('admin/dashboard.html.twig', [
           'allUsers' => $allUsers,
        ]);
    }
}
