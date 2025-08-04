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
        // Vérifier si l'admin existe déjà
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@gmail.com']);
        
        if (!$existingAdmin) {
            $admin = new User();
            $admin->setEmail('admin@gmail.com');
            $admin->setFirstName('Admin');
            $admin->setLastName('VideTonGrenier');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setIsVerified(true);
            $admin->setIsBanned(false);
            
            // Hash du mot de passe Prince0503!
            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'Prince0503!');
            $admin->setPassword($hashedPassword);
            
            $manager->persist($admin);
            $manager->flush();
            
            echo "Admin créé avec succès !\n";
            echo "Email: admin@gmail.com\n";
            echo "Mot de passe: Prince0503!\n";
        } else {
            echo "L'administrateur existe déjà.\n";
        }
    }
}