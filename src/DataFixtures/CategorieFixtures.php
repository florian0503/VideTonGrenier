<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Électronique' => 'Smartphones, ordinateurs, TV, appareils électroniques',
            'Meubles' => 'Mobilier pour la maison, bureau, décoration',
            'Vêtements' => 'Mode homme, femme, enfant, chaussures, accessoires',
            'Véhicules' => 'Voitures, motos, vélos, pièces automobiles',
            'Immobilier' => 'Appartements, maisons, terrains, bureaux',
            'Maison & Jardin' => 'Outils, plantes, équipements de jardinage',
            'Sports & Loisirs' => 'Équipements sportifs, jeux, hobbies',
            'Livres & Musique' => 'Livres, CD, vinyles, instruments de musique',
            'Emploi' => 'Offres d\'emploi, services professionnels',
            'Services' => 'Cours, réparations, aide à domicile',
        ];

        foreach ($categories as $nom => $description) {
            $existingCategorie = $manager->getRepository(Categorie::class)->findOneBy(['nom' => $nom]);

            if (!$existingCategorie) {
                $categorie = new Categorie();
                $categorie->setNom($nom);
                $categorie->setDescription($description);
                $categorie->setSlug(strtolower(str_replace([' ', '&'], ['-', '-'], $nom)));
                $categorie->setIsActive(true);

                $manager->persist($categorie);
            }
        }

        $manager->flush();
        echo "Catégories créées avec succès !\n";
    }
}