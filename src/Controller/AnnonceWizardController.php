<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/annonce/deposer')]
#[IsGranted('ROLE_USER')]
class AnnonceWizardController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    #[Route('/', name: 'annonce_wizard_start', methods: ['GET'])]
    public function start(Request $request): Response
    {
        $this->clearWizardSession($request);

        return $this->redirectToRoute('annonce_wizard_step1');
    }

    #[Route('/etape-1', name: 'annonce_wizard_step1', methods: ['GET', 'POST'])]
    public function step1(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $type = $request->request->get('type');

            if (!$titre || !$type) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
                return $this->render('annonce/wizard/step1.html.twig');
            }

            $request->getSession()->set('wizard_step1', [
                'titre' => $titre,
                'type' => $type
            ]);

            return $this->redirectToRoute('annonce_wizard_step2');
        }

        $data = $request->getSession()->get('wizard_step1', []);

        return $this->render('annonce/wizard/step1.html.twig', [
            'data' => $data
        ]);
    }

    #[Route('/etape-2', name: 'annonce_wizard_step2', methods: ['GET', 'POST'])]
    public function step2(Request $request, CategorieRepository $categorieRepository): Response
    {
        if (!$request->getSession()->has('wizard_step1')) {
            return $this->redirectToRoute('annonce_wizard_step1');
        }

        if ($request->isMethod('POST')) {
            $categorieId = $request->request->get('categorie_id');

            if (!$categorieId) {
                $this->addFlash('error', 'Veuillez sélectionner une catégorie.');
            } else {
                $request->getSession()->set('wizard_step2', [
                    'categorie_id' => $categorieId
                ]);
                return $this->redirectToRoute('annonce_wizard_step3');
            }
        }

        $categories = $categorieRepository->findBy(['isActive' => true], ['nom' => 'ASC']);
        $data = $request->getSession()->get('wizard_step2', []);

        return $this->render('annonce/wizard/step2.html.twig', [
            'categories' => $categories,
            'data' => $data
        ]);
    }

    #[Route('/etape-3', name: 'annonce_wizard_step3', methods: ['GET', 'POST'])]
    public function step3(Request $request): Response
    {
        if (!$request->getSession()->has('wizard_step1') || !$request->getSession()->has('wizard_step2')) {
            return $this->redirectToRoute('annonce_wizard_step1');
        }

        if ($request->isMethod('POST')) {
            $description = $request->request->get('description');
            $prix = $request->request->get('prix');
            $isUrgent = $request->request->get('is_urgent', false);

            if (!$description) {
                $this->addFlash('error', 'La description est obligatoire.');
            } else {
                $request->getSession()->set('wizard_step3', [
                    'description' => $description,
                    'prix' => $prix,
                    'is_urgent' => (bool)$isUrgent
                ]);
                return $this->redirectToRoute('annonce_wizard_step4');
            }
        }

        $data = $request->getSession()->get('wizard_step3', []);

        return $this->render('annonce/wizard/step3.html.twig', [
            'data' => $data
        ]);
    }

    #[Route('/etape-4', name: 'annonce_wizard_step4', methods: ['GET', 'POST'])]
    public function step4(Request $request): Response
    {
        if (!$request->getSession()->has('wizard_step1') ||
            !$request->getSession()->has('wizard_step2') ||
            !$request->getSession()->has('wizard_step3')) {
            return $this->redirectToRoute('annonce_wizard_step1');
        }

        if ($request->isMethod('POST')) {
            $images = $request->getSession()->get('wizard_step4_images', []);

            $request->getSession()->set('wizard_step4', [
                'images' => $images
            ]);

            return $this->redirectToRoute('annonce_wizard_step5');
        }

        $images = $request->getSession()->get('wizard_step4_images', []);

        return $this->render('annonce/wizard/step4.html.twig', [
            'images' => $images
        ]);
    }

    #[Route('/etape-5', name: 'annonce_wizard_step5', methods: ['GET', 'POST'])]
    public function step5(Request $request): Response
    {
        if (!$request->getSession()->has('wizard_step1') ||
            !$request->getSession()->has('wizard_step2') ||
            !$request->getSession()->has('wizard_step3') ||
            !$request->getSession()->has('wizard_step4')) {
            return $this->redirectToRoute('annonce_wizard_step1');
        }

        if ($request->isMethod('POST')) {
            $localisation = $request->request->get('localisation');
            $codePostal = $request->request->get('code_postal');
            $ville = $request->request->get('ville');

            if (!$localisation || !$codePostal || !$ville) {
                $this->addFlash('error', 'Tous les champs de localisation sont obligatoires.');
            } else {
                $request->getSession()->set('wizard_step5', [
                    'localisation' => $localisation,
                    'code_postal' => $codePostal,
                    'ville' => $ville
                ]);

                $annonce = $this->createAnnonceFromSession($request);

                if ($annonce) {
                    $this->clearWizardSession($request);
                    $this->addFlash('success', 'Votre annonce a été créée avec succès !');
                    return $this->redirectToRoute('app_annonce_show', ['id' => $annonce->getId()]);
                }
            }
        }

        $step1 = $request->getSession()->get('wizard_step1', []);
        $step2 = $request->getSession()->get('wizard_step2', []);
        $step3 = $request->getSession()->get('wizard_step3', []);
        $step4 = $request->getSession()->get('wizard_step4', []);
        $step5 = $request->getSession()->get('wizard_step5', []);

        return $this->render('annonce/wizard/step5.html.twig', [
            'step1' => $step1,
            'step2' => $step2,
            'step3' => $step3,
            'step4' => $step4,
            'data' => $step5
        ]);
    }

    #[Route('/upload-image', name: 'annonce_wizard_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier sélectionné'], 400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($uploadedFile->getMimeType(), $allowedMimes)) {
            return new JsonResponse(['error' => 'Format de fichier non autorisé'], 400);
        }

        if ($uploadedFile->getSize() > $maxSize) {
            return new JsonResponse(['error' => 'Fichier trop volumineux (max 2MB)'], 400);
        }

        try {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

            $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/annonces';
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            $uploadedFile->move($uploadDirectory, $newFilename);

            $images = $request->getSession()->get('wizard_step4_images', []);
            $images[] = $newFilename;
            $request->getSession()->set('wizard_step4_images', $images);

            return new JsonResponse([
                'success' => true,
                'filename' => $newFilename,
                'url' => '/uploads/annonces/' . $newFilename
            ]);

        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Erreur lors du téléchargement'], 500);
        }
    }

    #[Route('/remove-image', name: 'annonce_wizard_remove_image', methods: ['POST'])]
    public function removeImage(Request $request): JsonResponse
    {
        $filename = $request->request->get('filename');

        if (!$filename) {
            return new JsonResponse(['error' => 'Nom de fichier manquant'], 400);
        }

        $images = $request->getSession()->get('wizard_step4_images', []);
        $images = array_filter($images, fn($img) => $img !== $filename);
        $request->getSession()->set('wizard_step4_images', array_values($images));

        $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/annonces/'.$filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return new JsonResponse(['success' => true]);
    }

    private function createAnnonceFromSession(Request $request): ?Annonce
    {
        try {
            $step1 = $request->getSession()->get('wizard_step1');
            $step2 = $request->getSession()->get('wizard_step2');
            $step3 = $request->getSession()->get('wizard_step3');
            $step4 = $request->getSession()->get('wizard_step4');
            $step5 = $request->getSession()->get('wizard_step5');

            $images = $request->getSession()->get('wizard_step4_images', []);

            $categorie = $this->entityManager->getRepository(Categorie::class)->find($step2['categorie_id']);

            $annonce = new Annonce();
            $annonce->setTitre($step1['titre']);
            $annonce->setType($step1['type']);
            $annonce->setCategorie($categorie);
            $annonce->setDescription($step3['description']);
            $annonce->setPrix($step3['prix'] ?? null);
            $annonce->setIsUrgent($step3['is_urgent'] ?? false);
            $annonce->setImages($images);
            $annonce->setLocalisation($step5['localisation']);
            $annonce->setCodePostal($step5['code_postal']);
            $annonce->setVille($step5['ville']);
            $annonce->setUser($this->getUser());
            $annonce->setStatus(Annonce::STATUS_PENDING);
            $annonce->setPublishedAt(new \DateTime());

            $this->entityManager->persist($annonce);
            $this->entityManager->flush();

            return $annonce;

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de l\'annonce : ' . $e->getMessage());
            return null;
        }
    }

    private function clearWizardSession(Request $request = null): void
    {
        if (!$request) {
            return;
        }

        $session = $request->getSession();
        $session->remove('wizard_step1');
        $session->remove('wizard_step2');
        $session->remove('wizard_step3');
        $session->remove('wizard_step4');
        $session->remove('wizard_step4_images');
        $session->remove('wizard_step5');
    }
}
