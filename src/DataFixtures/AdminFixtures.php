<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // PRODUCTION: Ne pas créer d'administrateur par défaut
        // Les comptes administrateurs doivent être créés manuellement en production
        // pour des raisons de sécurité
        
        // Décommentez le code ci-dessous uniquement en développement:
        /*
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
        
        if (!$existingAdmin) {
            $admin = new User();
            $admin->setEmail('admin@example.com');
            $admin->setFirstName('Admin');
            $admin->setLastName('VideTonGrenier');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setIsVerified(true);
            $admin->setIsBanned(false);
            
            // Utilisez un mot de passe sécurisé et changez-le immédiatement
            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'ChangeMe123!');
            $admin->setPassword($hashedPassword);
            
            $manager->persist($admin);
            $manager->flush();
        }
        */
    }
}