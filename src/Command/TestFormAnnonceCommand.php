<?php

namespace App\Command;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\User;
use App\Form\AnnonceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Form\FormFactoryInterface;

#[AsCommand(
    name: 'app:test-form-annonce',
    description: 'Test la création d\'annonce comme dans le formulaire',
)]
class TestFormAnnonceCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer un utilisateur et une catégorie
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        $categorie = $this->entityManager->getRepository(Categorie::class)->findOneBy(['isActive' => true]);

        if (!$user || !$categorie) {
            $io->error('Utilisateur ou catégorie manquant.');
            return Command::FAILURE;
        }

        // Simuler exactement le processus du controller
        $annonce = new Annonce();
        $annonce->setUser($user);
        
        $io->writeln('Statut par défaut dans le constructeur: ' . ($annonce->getStatus() ?? 'NULL'));

        // Simuler les données du formulaire
        $annonce->setTitre('Test depuis commande form - ' . date('H:i:s'));
        $annonce->setDescription('Description de test pour simuler le formulaire web');
        $annonce->setPrix('99.99');
        $annonce->setType(Annonce::TYPE_SELL);
        $annonce->setCategorie($categorie);
        $annonce->setVille('Paris');
        $annonce->setCodePostal('75001');
        $annonce->setLocalisation('Paris centre');
        $annonce->setIsUrgent(false);

        $io->writeln('Statut AVANT setStatus: ' . ($annonce->getStatus() ?? 'NULL'));
        
        // Appliquer le statut comme dans le controller
        $annonce->setStatus(Annonce::STATUS_PENDING);
        
        $io->writeln('Statut APRÈS setStatus: ' . $annonce->getStatus());
        $io->writeln('Constante STATUS_PENDING: ' . Annonce::STATUS_PENDING);

        $this->entityManager->persist($annonce);
        
        $io->writeln('Statut AVANT flush: ' . $annonce->getStatus());
        
        $this->entityManager->flush();
        
        $io->writeln('Statut APRÈS flush: ' . $annonce->getStatus());

        // Vérifier en base
        $annonceFromDb = $this->entityManager->getRepository(Annonce::class)->find($annonce->getId());
        $io->writeln('Statut lu depuis la base: ' . $annonceFromDb->getStatus());

        $io->success('Test terminé - ID: ' . $annonce->getId());

        return Command::SUCCESS;
    }
}