<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/legal')]
final class LegalController extends AbstractController
{
    #[Route('/terms', name: 'app_legal_terms')]
    public function terms(): Response
    {
        return $this->render('legal/terms.html.twig');
    }

    #[Route('/privacy', name: 'app_legal_privacy')]
    public function privacy(): Response
    {
        return $this->render('legal/privacy.html.twig');
    }

    #[Route('/mentions', name: 'app_legal_mentions')]
    public function mentions(): Response
    {
        return $this->render('legal/mentions.html.twig');
    }

    #[Route('/about', name: 'app_legal_about')]
    public function about(): Response
    {
        return $this->render('legal/about.html.twig');
    }

    #[Route('/faq', name: 'app_legal_faq')]
    public function faq(): Response
    {
        return $this->render('legal/faq.html.twig');
    }
}
