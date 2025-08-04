<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AnnonceRepository $annonceRepository, CategorieRepository $categorieRepository): Response
    {
        // Récupérer les annonces récentes publiées
        $recentAnnonces = $annonceRepository->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', Annonce::STATUS_PUBLISHED)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        // Récupérer les 8 premières catégories actives avec le nombre d'annonces
        $categories = $categorieRepository->createQueryBuilder('c')
            ->select('c', 'COUNT(a.id) as annonceCount')
            ->leftJoin('c.annonces', 'a', 'WITH', 'a.status = :status')
            ->where('c.isActive = true')
            ->setParameter('status', Annonce::STATUS_PUBLISHED)
            ->groupBy('c.id')
            ->orderBy('c.nom', 'ASC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();

        // Compter le total de catégories actives
        $totalCategories = $categorieRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isActive = true')
            ->getQuery()
            ->getSingleScalarResult();

        // Compter le total d'annonces publiées
        $totalAnnonces = $annonceRepository->count(['status' => Annonce::STATUS_PUBLISHED]);

        return $this->render('home/index.html.twig', [
            'recentAnnonces' => $recentAnnonces,
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'totalAnnonces' => $totalAnnonces,
        ]);
    }
}
