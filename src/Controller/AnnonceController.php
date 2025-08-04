<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/annonces')]
final class AnnonceController extends AbstractController
{
    private const ANNONCES_PER_PAGE = 6;

    #[Route('/', name: 'app_annonce_index', methods: ['GET'])]
    public function index(AnnonceRepository $annonceRepository, CategorieRepository $categorieRepository, Request $request): Response
    {
        $categories = $categorieRepository->findBy(['isActive' => true]);

        // Récupération de tous les paramètres de recherche
        $filters = [
            'q' => $request->query->get('q'),
            'categorie' => $request->query->get('categorie'),
            'prix_min' => $request->query->get('prix_min'),
            'prix_max' => $request->query->get('prix_max'),
            'localisation' => $request->query->get('localisation'),
            'type' => $request->query->get('type'),
            'sort' => $request->query->get('sort', 'date_desc')
        ];

        $queryBuilder = $annonceRepository->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', Annonce::STATUS_PUBLISHED);

        // Filtre par catégorie
        if ($filters['categorie']) {
            $queryBuilder->andWhere('a.categorie = :categorie')
                ->setParameter('categorie', $filters['categorie']);
        }

        // Filtre par mot-clé
        if ($filters['q']) {
            $queryBuilder->andWhere('a.titre LIKE :search OR a.description LIKE :search')
                ->setParameter('search', '%' . $filters['q'] . '%');
        }

        // Filtre par prix minimum
        if ($filters['prix_min']) {
            $queryBuilder->andWhere('a.prix >= :prixMin')
                ->setParameter('prixMin', $filters['prix_min']);
        }

        // Filtre par prix maximum
        if ($filters['prix_max']) {
            $queryBuilder->andWhere('a.prix <= :prixMax')
                ->setParameter('prixMax', $filters['prix_max']);
        }

        // Filtre par localisation
        if ($filters['localisation']) {
            $queryBuilder->andWhere('a.ville LIKE :localisation OR a.localisation LIKE :localisation OR a.codePostal LIKE :localisation')
                ->setParameter('localisation', '%' . $filters['localisation'] . '%');
        }

        // Filtre par type
        if ($filters['type']) {
            $queryBuilder->andWhere('a.type = :type')
                ->setParameter('type', $filters['type']);
        }

        // Tri
        switch ($filters['sort']) {
            case 'date_asc':
                $queryBuilder->orderBy('a.publishedAt', 'ASC');
                break;
            case 'prix_asc':
                $queryBuilder->orderBy('a.prix', 'ASC');
                break;
            case 'prix_desc':
                $queryBuilder->orderBy('a.prix', 'DESC');
                break;
            case 'date_desc':
            default:
                $queryBuilder->orderBy('a.publishedAt', 'DESC');
                break;
        }

        // Compter le total d'annonces
        $totalAnnonces = (clone $queryBuilder)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        // Récupérer seulement les 6 premières annonces
        $annonces = $queryBuilder
            ->setMaxResults(self::ANNONCES_PER_PAGE)
            ->getQuery()
            ->getResult();

        $hasMore = $totalAnnonces > self::ANNONCES_PER_PAGE;

        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonces,
            'categories' => $categories,
            'filters' => $filters,
            'has_more' => $hasMore,
            'total_annonces' => $totalAnnonces,
        ]);
    }

    #[Route('/load-more', name: 'app_annonce_load_more', methods: ['GET'])]
    public function loadMore(AnnonceRepository $annonceRepository, Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $categoryFilter = $request->query->get('categorie');
        $search = $request->query->get('q');

        $offset = ($page - 1) * self::ANNONCES_PER_PAGE;

        $queryBuilder = $annonceRepository->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', Annonce::STATUS_PUBLISHED)
            ->orderBy('a.publishedAt', 'DESC');

        if ($categoryFilter) {
            $queryBuilder->andWhere('a.categorie = :categorie')
                ->setParameter('categorie', $categoryFilter);
        }

        if ($search) {
            $queryBuilder->andWhere('a.titre LIKE :search OR a.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Compter le total
        $totalAnnonces = (clone $queryBuilder)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        // Récupérer les annonces pour cette page
        $annonces = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults(self::ANNONCES_PER_PAGE)
            ->getQuery()
            ->getResult();

        $hasMore = ($offset + self::ANNONCES_PER_PAGE) < $totalAnnonces;

        // Rendre les annonces en HTML
        $html = $this->renderView('annonce/_annonce_cards.html.twig', [
            'annonces' => $annonces
        ]);

        return new JsonResponse([
            'html' => $html,
            'hasMore' => $hasMore,
            'currentPage' => $page,
            'totalAnnonces' => $totalAnnonces
        ]);
    }

    #[Route('/{id}', name: 'app_annonce_show', methods: ['GET'])]
    public function show(Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        $annonce->incrementVues();
        $entityManager->flush();

        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_annonce_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Annonce $annonce, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): Response
    {
        if ($annonce->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres annonces.');
        }

        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $prix = $request->request->get('prix');
            $categorieId = $request->request->get('categorie_id');
            $type = $request->request->get('type');
            $localisation = $request->request->get('localisation');
            $codePostal = $request->request->get('code_postal');
            $ville = $request->request->get('ville');
            $isUrgent = (bool)$request->request->get('is_urgent', false);

            if (!$titre || !$description || !$categorieId || !$type || !$localisation || !$codePostal || !$ville) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            } else {
                $categorie = $categorieRepository->find($categorieId);
                if (!$categorie) {
                    $this->addFlash('error', 'Catégorie invalide.');
                } else {
                    $annonce->setTitre($titre);
                    $annonce->setDescription($description);
                    $annonce->setPrix($prix ? floatval($prix) : null);
                    $annonce->setCategorie($categorie);
                    $annonce->setType($type);
                    $annonce->setLocalisation($localisation);
                    $annonce->setCodePostal($codePostal);
                    $annonce->setVille($ville);
                    $annonce->setIsUrgent($isUrgent);

                    $entityManager->flush();

                    $this->addFlash('success', 'Votre annonce a été modifiée avec succès !');
                    return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
                }
            }
        }

        $categories = $categorieRepository->findBy(['isActive' => true], ['nom' => 'ASC']);

        return $this->render('annonce/edit.html.twig', [
            'annonce' => $annonce,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_annonce_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        if ($annonce->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres annonces.');
        }

        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($annonce);
            $entityManager->flush();

            $this->addFlash('success', 'Votre annonce a été supprimée avec succès !');
        }

        return $this->redirectToRoute('app_annonce_index');
    }

    #[Route('/{id}/marquer-vendue', name: 'app_annonce_mark_sold', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markSold(Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        if ($annonce->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez marquer comme vendue que vos propres annonces.');
        }

        if ($annonce->isPublished()) {
            $annonce->setStatus(Annonce::STATUS_SOLD);
            $entityManager->flush();

            $this->addFlash('success', sprintf('L\'annonce "%s" a été marquée comme vendue !', $annonce->getTitre()));
        } else {
            $this->addFlash('error', 'Seules les annonces publiées peuvent être marquées comme vendues.');
        }

        return $this->redirectToRoute('app_user_annonces');
    }
}
