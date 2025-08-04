<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BannedController extends AbstractController
{
    #[Route('/compte-suspendu', name: 'app_banned')]
    #[IsGranted('ROLE_USER')]
    public function banned(): Response
    {
        $user = $this->getUser();
        
        // Si l'utilisateur n'est pas banni, rediriger vers l'accueil
        if (!$user->isBanned()) {
            return $this->redirectToRoute('app_home');
        }
        
        return $this->render('banned/index.html.twig', [
            'user' => $user,
        ]);
    }
}