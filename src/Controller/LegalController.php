<?php

namespace App\Controller;

use App\Service\RawgClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class LegalController extends AbstractController
{
    #[Route('/legal/conditions', name: 'legal_conditions')]
    public function showConditions(): Response
    {
        return $this->render('legal/conditions.html.twig');
    }

    #[Route('/legal/privacy', name: 'legal_privacy')]
    public function showPrivacy(): Response
    {
        return $this->render('legal/privacy.html.twig');
    }
}
