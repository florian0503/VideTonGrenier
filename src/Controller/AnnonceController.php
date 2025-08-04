<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Form\AnnonceType;
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

    #[Route('/nouvelle', name: 'app_annonce_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $annonce = new Annonce();
        $annonce->setUser($this->getUser());
        
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $annonce->setStatus(Annonce::STATUS_PENDING);
            
            $entityManager->persist($annonce);
            $entityManager->flush();

            $this->addFlash('success', 'Votre annonce a été soumise avec succès ! Elle sera visible après modération.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('annonce/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
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
    public function edit(Request $request, Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est le propriétaire
        if ($annonce->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres annonces.');
        }

        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $annonce->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Votre annonce a été mise à jour avec succès !');

            return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
        }

        return $this->render('annonce/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
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