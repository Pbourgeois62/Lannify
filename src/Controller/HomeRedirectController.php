<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/home', name: 'home')]
#[IsGranted('ROLE_USER')]
class HomeRedirectController extends AbstractController
{
    public function __invoke(#[CurrentUser] User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return $this->redirectToRoute('admin_home');
        }

        return $this->redirectToRoute('user_home');
    }
}
