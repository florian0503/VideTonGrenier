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
        $recentAnnonces = $annonceRepository->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', Annonce::STATUS_PUBLISHED)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        $categories = $categorieRepository->createQueryBuilder('c')
            ->select('c', 'COUNT(a.id) as annonceCount')
            ->leftJoin('c.annonces', 'a', 'WITH', 'a.status = :status')
            ->where('c.isActive = true')
            ->setParameter('status', Annonce::STATUS_PUBLISHED)
            ->groupBy('c.id')
            ->orderBy('annonceCount', 'DESC')
            ->addOrderBy('c.nom', 'ASC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();

        $totalCategories = $categorieRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isActive = true')
            ->getQuery()
            ->getSingleScalarResult();

        $totalAnnonces = $annonceRepository->count(['status' => Annonce::STATUS_PUBLISHED]);

        return $this->render('home/index.html.twig', [
            'recentAnnonces' => $recentAnnonces,
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'totalAnnonces' => $totalAnnonces,
        ]);
    }
}
