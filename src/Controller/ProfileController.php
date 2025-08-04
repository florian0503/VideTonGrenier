<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        // Statistiques utilisateur
        $userStats = [
            'totalAnnonces' => $user->getAnnonces()->count(),
            'annoncesPubliees' => $user->getAnnonces()->filter(fn($a) => $a->isPublished())->count(),
            'annoncesPendantes' => $user->getAnnonces()->filter(fn($a) => $a->isPending())->count(),
            'membresDepuis' => $user->getCreatedAt()
        ];

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'stats' => $userStats,
        ]);
    }

    #[Route('/modifier', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
            
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/mes-annonces', name: 'app_user_annonces')]
    public function mesAnnonces(): Response
    {
        $user = $this->getUser();
        $annonces = $user->getAnnonces();

        // Grouper par statut
        $annoncesByStatus = [
            'published' => $annonces->filter(fn($a) => $a->isPublished()),
            'pending' => $annonces->filter(fn($a) => $a->isPending()),
            'rejected' => $annonces->filter(fn($a) => $a->isRejected()),
            'sold' => $annonces->filter(fn($a) => $a->isSold()),
        ];

        return $this->render('profile/mes-annonces.html.twig', [
            'user' => $user,
            'annoncesByStatus' => $annoncesByStatus,
        ]);
    }
}