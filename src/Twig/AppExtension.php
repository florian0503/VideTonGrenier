<?php

namespace App\Twig;

use App\Repository\CategorieRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private CategorieRepository $categorieRepository
    ) {}

    public function getGlobals(): array
    {
        return [
            'categories' => $this->categorieRepository->findBy(['isActive' => true], ['nom' => 'ASC']),
        ];
    }
}
