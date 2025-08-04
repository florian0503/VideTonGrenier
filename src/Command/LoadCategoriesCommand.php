<?php

namespace App\Command;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:load-categories',
    description: 'Charge les catégories par défaut dans la base de données',
)]
class LoadCategoriesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categories = [
            ['nom' => 'Électronique', 'description' => 'Téléphones, ordinateurs, consoles, TV...', 'icone' => '📱'],
            ['nom' => 'Mobilier', 'description' => 'Meubles, décoration, électroménager...', 'icone' => '🪑'],
            ['nom' => 'Mode', 'description' => 'Vêtements, chaussures, accessoires...', 'icone' => '👕'],
            ['nom' => 'Sports & Loisirs', 'description' => 'Équipements sportifs, jeux, livres...', 'icone' => '⚽'],
            ['nom' => 'Maison & Jardin', 'description' => 'Bricolage, jardinage, décoration...', 'icone' => '🏠'],
            ['nom' => 'Véhicules', 'description' => 'Voitures, motos, vélos...', 'icone' => '🚗'],
            ['nom' => 'Emploi & Services', 'description' => 'Offres d\'emploi, services à la personne...', 'icone' => '💼'],
            ['nom' => 'Immobilier', 'description' => 'Vente, location, colocation...', 'icone' => '🏘️'],
        ];

        $io->title('Chargement des catégories par défaut');

        foreach ($categories as $categoryData) {
            // Vérifier si la catégorie existe déjà
            $existingCategory = $this->entityManager->getRepository(Categorie::class)
                ->findOneBy(['nom' => $categoryData['nom']]);

            if ($existingCategory) {
                $io->text(sprintf('⚠️  Catégorie "%s" existe déjà', $categoryData['nom']));
                continue;
            }

            $category = new Categorie();
            $category->setNom($categoryData['nom']);
            $category->setDescription($categoryData['description']);
            $category->setSlug($this->slugger->slug($categoryData['nom'])->lower());
            $category->setIcone($categoryData['icone']);
            $category->setIsActive(true);

            $this->entityManager->persist($category);
            $io->text(sprintf('✅ Catégorie "%s" créée', $categoryData['nom']));
        }

        $this->entityManager->flush();

        $io->success('Toutes les catégories ont été chargées avec succès !');

        return Command::SUCCESS;
    }
}
