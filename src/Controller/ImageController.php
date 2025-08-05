<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    #[Route('/uploads/annonces/{filename}', name: 'app_image_annonce', methods: ['GET'])]
    public function serveAnnonceImage(string $filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/annonces/' . $filename;
        
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Image not found');
        }
        
        return new BinaryFileResponse($filePath);
    }
}