<?php

namespace App\Command;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-annonce',
    description: 'Crée une annonce de test pour vérifier le système de modération',
)]
class CreateTestAnnonceCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer un utilisateur
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        if (!$user) {
            $io->error('Aucun utilisateur trouvé en base de données.');
            return Command::FAILURE;
        }

        // Récupérer une catégorie
        $categorie = $this->entityManager->getRepository(Categorie::class)->findOneBy(['isActive' => true]);
        if (!$categorie) {
            $io->error('Aucune catégorie active trouvée en base de données.');
            return Command::FAILURE;
        }

        // Créer une nouvelle annonce
        $annonce = new Annonce();
        $annonce->setTitre('Test annonce depuis commande - ' . date('H:i:s'))
            ->setDescription('Cette annonce a été créée via la commande de test pour vérifier le système de modération.')
            ->setPrix('150.00')
            ->setType(Annonce::TYPE_SELL)
            ->setCategorie($categorie)
            ->setUser($user)
            ->setLocalisation('Test location')
            ->setCodePostal('75001')
            ->setVille('Paris Test')
            ->setIsUrgent(false);
        
        // Définir explicitement le statut APRÈS les autres propriétés
        $annonce->setStatus(Annonce::STATUS_PENDING);

        $this->entityManager->persist($annonce);
        $this->entityManager->flush();

        $io->success(sprintf('Annonce de test créée avec succès (ID: %d, Statut: %s)', 
            $annonce->getId(), 
            $annonce->getStatus()
        ));

        return Command::SUCCESS;
    }
}