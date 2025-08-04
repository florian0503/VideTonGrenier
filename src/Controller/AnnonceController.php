<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/annonces')]
final class AnnonceController extends AbstractController
{
    #[Route('/', name: 'app_annonce_index', methods: ['GET'])]
    public function index(AnnonceRepository $annonceRepository, CategorieRepository $categorieRepository, Request $request): Response
    {
        $categories = $categorieRepository->findBy(['isActive' => true]);
        $categoryFilter = $request->query->get('categorie');
        $search = $request->query->get('q');

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

        $annonces = $queryBuilder->getQuery()->getResult();

        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonces,
            'categories' => $categories,
            'current_category' => $categoryFilter,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'app_annonce_show', methods: ['GET'])]
    public function show(Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        // Incrémenter les vues
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
        // Vérifier que l'utilisateur est le propriétaire
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

            // Validation
            if (!$titre || !$description || !$categorieId || !$type || !$localisation || !$codePostal || !$ville) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            } else {
                $categorie = $categorieRepository->find($categorieId);
                if (!$categorie) {
                    $this->addFlash('error', 'Catégorie invalide.');
                } else {
                    // Mettre à jour l'annonce
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
        // Vérifier que l'utilisateur est le propriétaire
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
        // Vérifier que l'utilisateur est le propriétaire
        if ($annonce->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez marquer comme vendue que vos propres annonces.');
        }

        // Marquer comme vendue seulement si elle est publiée
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