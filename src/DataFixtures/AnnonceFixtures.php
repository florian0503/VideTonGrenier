<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AnnonceFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;
    private array $users = [];
    private int $userCounter = 0;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Récupérer toutes les catégories
        $categories = $manager->getRepository(Categorie::class)->findAll();

        if (empty($categories)) {
            // Aucune catégorie trouvée. Veuillez d'abord charger les fixtures de catégories.
            return;
        }

        // Données d'annonces plus variées avec photos
        $annonceData = [
            'Électronique' => [
                ['iPhone 14 Pro Max 256Go', 'iPhone 14 Pro Max Violet Intense, 256Go. Acheté il y a 8 mois, toujours sous garantie Apple. Livré avec boîte d\'origine, chargeur Lightning et coque de protection. Écran et dos impeccables, aucune rayure visible. Batterie à 96% de capacité.', 899, ['iphone-14-pro-1.jpg', 'iphone-14-pro-2.jpg']],
                ['MacBook Air M2 13"', 'MacBook Air M2 2022, couleur Argent. 8Go de RAM, SSD 512Go. Parfait pour étudiant en informatique ou usage professionnel. Très peu utilisé (environ 50 cycles de batterie). Vendu avec chargeur MagSafe et housse en cuir.', 1150, ['macbook-air-m2-1.jpg', 'macbook-air-m2-2.jpg', 'macbook-air-m2-3.jpg']],
                ['Smart TV Samsung 65" QLED', 'Téléviseur Samsung QE65Q80B 2022, technologie QLED 4K. HDR10+, Dolby Vision. Acheté en janvier 2023, encore sous garantie. Livré avec télécommande, pied et tous les câbles. Quelques traces sur le pied, écran parfait.', 799, ['tv-samsung-65-1.jpg', 'tv-samsung-65-2.jpg']],
                ['PlayStation 5 + 4 jeux', 'Console PS5 standard achetée en novembre 2022. Très bon état, aucun problème technique. Vendue avec 4 jeux : Spider-Man Miles Morales, Horizon Forbidden West, FIFA 23 et Call of Duty MW2. Manette supplémentaire DualSense incluse.', 649, ['ps5-bundle-1.jpg', 'ps5-bundle-2.jpg', 'ps5-games.jpg']],
                ['AirPods Pro 2ème gen', 'AirPods Pro 2ème génération, achetés en décembre 2022. Réduction de bruit active excellente. Boîtier de charge MagSafe. Embouts en parfait état + embouts de rechange neufs. Son cristallin, autonomie parfaite.', 199, ['airpods-pro-2-1.jpg']],
                ['iPad Air 5 + Apple Pencil', 'iPad Air 5ème génération 64Go WiFi, couleur Violet. Acheté pour les études mais finalement peu utilisé. Livré avec Apple Pencil 2ème génération et clavier Smart Keyboard. Protection écran posée dès l\'achat.', 549, ['ipad-air-5-1.jpg', 'ipad-air-5-2.jpg', 'apple-pencil.jpg']],
                ['Nintendo Switch OLED', 'Console Nintendo Switch modèle OLED, écran magnifique. Achetée pour mon fils mais il préfère sa PS5. Très peu utilisée, comme neuve. Vendue avec 2 jeux : Mario Kart 8 et Super Mario Odyssey.', 279, ['switch-oled-1.jpg', 'switch-oled-2.jpg']],
                ['Casque Bose QuietComfort 45', 'Casque audio Bose QC45 avec réduction de bruit active. Acheté il y a 6 mois, utilisé occasionnellement. Autonomie 24h, son exceptionnel. Étui de transport et câbles inclus.', 249, ['bose-qc45-1.jpg', 'bose-qc45-2.jpg']]
            ],
            'Vêtements' => [
                ['Veste Schott en cuir noir', 'Authentique perfecto Schott NYC taille M (correspond à du L français). Cuir d\'agneau véritable, doublure matelassée. Portée 2-3 fois seulement, état neuf. Modèle iconique, intemporel. Facture d\'achat disponible.', 189, ['schott-jacket-1.jpg', 'schott-jacket-2.jpg']],
                ['Robe Zara collection limitée', 'Superbe robe de soirée Zara, collection studio, taille 36. Couleur noir avec détails dorés. Portée une seule fois pour un mariage. Très élégante, coupe parfaite. Nettoyage à sec effectué.', 45, ['robe-zara-1.jpg', 'robe-zara-2.jpg']],
                ['Sneakers Nike Air Jordan 1', 'Paire de Air Jordan 1 Mid coloris "Chicago", taille 43. Portées quelques fois en intérieur seulement. Semelles comme neuves, cuir en excellent état. Boîte d\'origine + lacets de rechange.', 139, ['jordan-1-1.jpg', 'jordan-1-2.jpg', 'jordan-1-box.jpg']],
                ['Manteau The North Face', 'Doudoune The North Face Nuptse 1996, taille L, couleur noir. Isolation duvet 700. Parfaite pour l\'hiver, très chaude. Quelques traces d\'usage mais rien de visible. Capuche amovible.', 169, ['tnf-nuptse-1.jpg', 'tnf-nuptse-2.jpg']],
                ['Costume Hugo Boss', 'Costume Hugo Boss 3 pièces, taille 50, couleur bleu marine. Laine Super 120s. Porté 3-4 fois pour des événements professionnels. Retouches effectuées par un tailleur. Très belle coupe.', 299, ['hugo-boss-1.jpg', 'hugo-boss-2.jpg', 'hugo-boss-3.jpg']],
                ['Jean Levi\'s 501 vintage', 'Jean Levi\'s 501 original vintage années 90, taille W32/L34. Délavage naturel superbe, aucun trou. Pièce de collection pour amateur de denim. Coupe droite classique.', 85, ['levis-501-1.jpg', 'levis-501-2.jpg']]
            ],
            'Maison & Jardin' => [
                ['Canapé Ikea Ektorp 3 places', 'Canapé Ikea Ektorp 3 places, couleur beige. Très confortable, parfait pour famille. Housses lavables en machine (2 jeux de housses inclus). Quelques traces d\'usage normal mais structure impeccable.', 149, ['canape-ektorp-1.jpg', 'canape-ektorp-2.jpg']],
                ['Table à manger vintage', 'Magnifique table danoise années 70 en teck massif. 6 personnes, rallonges intégrées (jusqu\'à 8 personnes). Restaurée avec amour, vernis satiné. Pièce de collection, très bon investissement.', 450, ['table-teck-1.jpg', 'table-teck-2.jpg', 'table-teck-3.jpg']],
                ['Bibliothèque sur mesure', 'Bibliothèque murale sur mesure, 6 étagères. Bois de chêne massif, finition huile naturelle. Dimensions : L200 x H220 x P30cm. Réalisée par un ébéniste, fixations murales invisibles incluses.', 380, ['bibliotheque-1.jpg', 'bibliotheque-2.jpg']],
                ['Barbecue Weber Genesis', 'Barbecue à gaz Weber Genesis E-315, 3 brûleurs. Acheté l\'été dernier, très peu utilisé (5-6 fois). Grille en fonte émaillée, thermomètre intégré. Plancha incluse. Housse de protection fournie.', 649, ['weber-genesis-1.jpg', 'weber-genesis-2.jpg', 'weber-plancha.jpg']],
                ['Salon de jardin teck', 'Ensemble salon de jardin en teck grade A : table ronde Ø120cm + 4 fauteuils avec coussins. Résistant aux intempéries. Utilisé une saison, excellent état. Coussins lavés et stockés à l\'abri.', 299, ['salon-jardin-1.jpg', 'salon-jardin-2.jpg']],
                ['Robot tondeuse Husqvarna', 'Robot tondeuse Husqvarna Automower 315X. Terrain jusqu\'à 1500m². Installation professionnelle effectuée. 2 ans d\'âge, fonctionne parfaitement. Antivol GPS intégré. Station de charge incluse.', 1450, ['robot-tondeuse-1.jpg', 'robot-tondeuse-2.jpg']]
            ],
            'Véhicules' => [
                ['VTT électrique Specialized', 'VTT électrique Specialized Turbo Levo SL. Moteur Specialized SL 1.1, batterie 320Wh. Transmission Shimano SLX 12v. Acheté en 2022, 1200km au compteur. Révision complète effectuée. Casque et antivol inclus.', 3200, ['vtt-elec-1.jpg', 'vtt-elec-2.jpg', 'vtt-elec-3.jpg']],
                ['Yamaha MT-07 2021', 'Moto Yamaha MT-07 modèle 2021, 14 500km. Entretien suivi en concession, carnet à jour. Quelques micro-rayures sur le réservoir. Pneus Michelin neufs. 2ème propriétaire, facture d\'achat disponible.', 5900, ['yamaha-mt07-1.jpg', 'yamaha-mt07-2.jpg', 'yamaha-mt07-3.jpg']],
                ['Vélo route carbone Trek', 'Vélo de route Trek Émonda SL5 2020. Cadre carbone, groupe Shimano 105 R7000. Roues Bontrager Paradigm. Très bon état, environ 3000km. Révision récente, chaîne et cassette neuves.', 1450, ['trek-emonda-1.jpg', 'trek-emonda-2.jpg']],
                ['Trottinette électrique Xiaomi', 'Trottinette Xiaomi Mi Electric Scooter Pro 2. Autonomie 45km, vitesse max 25km/h. Achetée il y a 8 mois, très bon état. Quelques éraflures sur le plateau. Chargeur et manuel inclus.', 320, ['xiaomi-scooter-1.jpg', 'xiaomi-scooter-2.jpg']],
                ['Peugeot 208 GTI 2020', 'Peugeot 208 GTI 1.6L THP 200ch, 35 000km. Première main, entretien Peugeot. Couleur Rouge Rubis. Intérieur cuir/alcantara. Jantes 17", climatisation auto. Contrôle technique OK.', 18900, ['peugeot-208-gti-1.jpg', 'peugeot-208-gti-2.jpg', 'peugeot-208-gti-3.jpg']]
            ],
            'Enfants' => [
                ['Poussette Cybex Priam', 'Poussette Cybex Priam châssis noir, nacelle et siège auto inclus. Système 3-en-1 complet. Très bon état, utilisée pour notre premier enfant. Roues toutes terrains, suspension excellente.', 450, ['cybex-priam-1.jpg', 'cybex-priam-2.jpg', 'cybex-priam-3.jpg']],
                ['Lot vêtements bébé 0-6 mois', 'Gros lot de vêtements bébé garçon 0-6 mois. Marques : H&M, Zara, Vertbaudet. Plus de 50 pièces : bodies, pyjamas, pulls, pantalons. Très bon état, lavage en lessive bébé uniquement.', 89, ['vetements-bebe-1.jpg', 'vetements-bebe-2.jpg']],
                ['Tricycle évolutif Puky', 'Tricycle évolutif Puky CAT 1 SP rouge. De 2 à 4 ans, hauteur de selle réglable. Roues silencieuses, freins à rétropédalage. Utilisé par notre fille, très bon état. Notice de montage incluse.', 75, ['puky-tricycle-1.jpg', 'puky-tricycle-2.jpg']],
                ['Parc bébé Chicco', 'Parc Chicco Open Sea Dreams, très pratique et sécurisé. Utilisé 6 mois, excellent état. Facile à monter/démonter. Matelas et jouets d\'éveil inclus. Lavable en machine.', 65, ['parc-chicco-1.jpg', 'parc-chicco-2.jpg']]
            ],
            'Meubles' => [
                ['Canapé cuir 3 places', 'Magnifique canapé en cuir véritable, 3 places, couleur marron cognac. Très confortable, structure en bois massif. Quelques traces d\'usage normal mais cuir en excellent état. Cause déménagement.', 450, ['canape-cuir-1.jpg', 'canape-cuir-2.jpg']],
                ['Table basse design', 'Table basse style scandinave en chêne massif et métal noir. Dimensions : 120x60x45cm. Achetée chez Made.com, très peu utilisée. Parfaite pour salon moderne.', 180, ['table-basse-1.jpg', 'table-basse-2.jpg']],
                ['Armoire 3 portes', 'Grande armoire 3 portes en pin massif, finition cirée. Hauteur 200cm, largeur 150cm. Parfaite pour chambre ou dressing. Démontable pour transport facilité.', 220, ['armoire-1.jpg', 'armoire-2.jpg']],
                ['Commode vintage', 'Commode années 60 en teck, 4 tiroirs. Restaurée avec soin, poignées d\'origine. Pièce de collection pour amateur de mobilier vintage. Très bon état général.', 380, ['commode-vintage-1.jpg', 'commode-vintage-2.jpg']]
            ],
            'Immobilier' => [
                ['Appartement T3 Lyon', 'Bel appartement T3 de 65m² dans le 3ème arrondissement de Lyon. 2 chambres, salon, cuisine équipée. Proche métro et commerces. Libre de suite. Visite virtuelle disponible.', 180000, ['appart-lyon-1.jpg', 'appart-lyon-2.jpg', 'appart-lyon-3.jpg']],
                ['Maison avec jardin', 'Charmante maison de 120m² avec jardin de 400m². 4 pièces, garage, cave. Quartier calme proche écoles. Travaux de rénovation récents. Idéale famille.', 285000, ['maison-1.jpg', 'maison-2.jpg', 'maison-jardin.jpg']],
                ['Studio centre-ville', 'Studio de 25m² en centre-ville, parfait pour investissement locatif ou pied-à-terre. Entièrement rénové, cuisine équipée, salle d\'eau moderne. Charges faibles.', 89000, ['studio-1.jpg', 'studio-2.jpg']],
                ['Terrain constructible', 'Terrain de 800m² en zone constructible, viabilisé. Exposition sud, vue dégagée. Proche commodités et transports. Certificat d\'urbanisme favorable pour construction individuelle.', 65000, ['terrain-1.jpg', 'terrain-2.jpg']]
            ],
            'Livres & Musique' => [
                ['Collection Harry Potter', 'Intégrale Harry Potter en français, édition originale Gallimard. 7 tomes en très bon état, jaquettes conservées. Parfait pour collectionneur ou cadeau.', 85, ['harry-potter-1.jpg', 'harry-potter-2.jpg']],
                ['Vinyles années 70-80', 'Lot de 50 vinyles années 70-80 : Pink Floyd, Led Zeppelin, Queen, Beatles... Bon à très bon état général. Quelques pièces rares incluses. Idéal mélomane.', 250, ['vinyles-1.jpg', 'vinyles-2.jpg', 'vinyles-3.jpg']],
                ['Piano numérique Yamaha', 'Piano numérique Yamaha P-125, 88 touches lestées. Excellent pour débutant comme confirmé. Très peu utilisé, avec pupitre et pédale sustain. Notice incluse.', 480, ['piano-yamaha-1.jpg', 'piano-yamaha-2.jpg']],
                ['Encyclopédie Larousse', 'Encyclopédie Larousse 20 volumes, édition 2015. Parfait état, très peu consultée. Idéale pour études ou culture générale. Vendue cause déménagement.', 120, ['larousse-1.jpg', 'larousse-2.jpg']]
            ],
            'Emploi' => [
                ['Développeur Web Junior', 'Startup recherche développeur web junior React/Node.js. Formation assurée, équipe jeune et dynamique. Télétravail possible 2j/semaine. Salaire 32-38K selon profil.', 35000, ['dev-web-1.jpg', 'dev-web-2.jpg']],
                ['Professeur mathématiques', 'Collège privé recherche professeur de mathématiques pour classes de 4ème/3ème. Temps plein, CDI. Expérience souhaitée mais débutant accepté. Poste à pourvoir rapidement.', 2200, ['prof-maths-1.jpg', 'prof-maths-2.jpg']],
                ['Commercial terrain H/F', 'Entreprise de matériel électrique recrute commercial(e) pour secteur Lyon/Rhône. Expérience vente BtoB exigée. Voiture de fonction, variable attractif.', 42000, ['commercial-1.jpg', 'commercial-2.jpg']],
                ['Aide soignante nuit', 'EHPAD recherche aide-soignante pour équipe de nuit. Diplôme exigé, expérience gériatrie appréciée. Planning aménageable, équipe bienveillante.', 1800, ['aide-soignante-1.jpg', 'aide-soignante-2.jpg']]
            ],
            'Services' => [
                ['Cours particuliers maths', 'Professeur agrégé propose cours particuliers mathématiques niveau collège/lycée. 15 ans d\'expérience. Méthode personnalisée, suivi régulier. Disponible soirées et week-ends.', 25, ['cours-maths-1.jpg', 'cours-maths-2.jpg']],
                ['Service ménage domicile', 'Dame de confiance propose service ménage à domicile sur Paris et proche banlieue. Expérience 10 ans, références sérieuses. Repassage, ménage complet. Cesu accepté.', 18, ['menage-1.jpg', 'menage-2.jpg']],
                ['Garde d\'enfants', 'Étudiante en école d\'infirmière propose garde d\'enfants le soir et week-ends. Expérience 5 ans, BAFA + PSC1. Aide aux devoirs possible. Secteur 15ème/16ème.', 12, ['garde-enfants-1.jpg', 'garde-enfants-2.jpg']],
                ['Réparation informatique', 'Technicien informatique propose réparation PC/Mac à domicile. Diagnostic gratuit, devis transparent. Spécialisé virus, récupération données, installation. Intervention rapide.', 45, ['reparation-pc-1.jpg', 'reparation-pc-2.jpg']]
            ],
            'Sports & Loisirs' => [
                ['Guitare acoustique Martin', 'Guitare Martin D-28 Standard, épicéa/palissandre. Son exceptionnel, lutherie américaine. Achetée neuve 2800€, très peu jouée. Étui rigide inclus. Parfaite pour enregistrement ou concert.', 1890, ['martin-d28-1.jpg', 'martin-d28-2.jpg', 'martin-case.jpg']],
                ['Raquette tennis Babolat', 'Raquette Babolat Pure Drive 2021, grip 3, cordée avec Luxilon Big Banger. Utilisée une saison en club. Très bon état, quelques traces normales d\'usage. Housse et surgrip neufs inclus.', 89, ['babolat-pure-1.jpg', 'babolat-pure-2.jpg']],
                ['Objectif Canon 24-70mm', 'Objectif Canon EF 24-70mm f/2.8L USM. Optique professionnelle, très polyvalent. Utilisé avec précaution, aucun impact. Lentilles parfaites, mise au point silencieuse. Pare-soleil et étui inclus.', 899, ['canon-24-70-1.jpg', 'canon-24-70-2.jpg']],
                ['Planche surf 6\'2"', 'Planche de surf 6\'2" x 19"1/4 x 2"7/16, 28L. Shape custom par un shaper local. Mousse EPS, résine époxy. Quelques coups normaux réparés proprement. Grip neuf, dérives FCS incluses.', 380, ['surf-board-1.jpg', 'surf-board-2.jpg', 'surf-board-3.jpg']]
            ]
        ];

        // NOUVELLE LOGIQUE : Garantir au minimum 4 annonces par catégorie
        $nombreAnnoncesCrees = 0;
        $maxAnnonces = 60;
        $annoncesParCategorie = [];

        // Étape 1 : Créer au minimum 4 annonces pour chaque catégorie
        foreach ($categories as $categorie) {
            $nomCategorie = $categorie->getNom();
            $annoncesParCategorie[$nomCategorie] = 0;

            if (isset($annonceData[$nomCategorie])) {
                $annoncesDisponibles = $annonceData[$nomCategorie];

                // Créer 4 annonces minimum pour cette catégorie
                for ($i = 0; $i < 4 && $nombreAnnoncesCrees < $maxAnnonces; $i++) {
                    $annonceInfo = $annoncesDisponibles[$i % count($annoncesDisponibles)];

                    $user = $this->getRandomUser($manager);
                    $annonce = new Annonce();

                    // Si on réutilise une annonce, modifier le titre
                    $titre = $annonceInfo[0];
                    if ($i >= count($annoncesDisponibles)) {
                        $suffixes = [' - Excellent état', ' - Prix négociable', ' - Cause déménagement'];
                        $titre .= $suffixes[$i % count($suffixes)];
                    }

                    $annonce->setTitre($titre);
                    $annonce->setDescription($annonceInfo[1]);

                    // Varier légèrement le prix si c'est une réutilisation
                    $prix = $annonceInfo[2];
                    if ($i >= count($annoncesDisponibles)) {
                        $variation = rand(-10, 10);
                        $prix = max(1, $prix + $variation);
                    }
                    $annonce->setPrix($prix);

                    $annonce->setType(Annonce::TYPE_SELL);
                    $annonce->setStatus(rand(0, 10) > 2 ? Annonce::STATUS_PUBLISHED : Annonce::STATUS_PENDING);
                    $annonce->setUser($user);
                    $annonce->setCategorie($categorie);

                    // Localisation variée
                    $villes = ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille'];
                    $ville = $villes[array_rand($villes)];
                    $codePostal = $this->getCodePostalForVille($ville);

                    $annonce->setVille($ville);
                    $annonce->setCodePostal($codePostal);
                    $annonce->setLocalisation($ville . ', France');

                    // Dates de création variées
                    $joursEcoules = rand(1, 90);
                    $createdAt = new \DateTime('-' . $joursEcoules . ' days');
                    $annonce->setCreatedAt($createdAt);

                    if ($annonce->getStatus() === Annonce::STATUS_PUBLISHED) {
                        $publishedAt = clone $createdAt;
                        $publishedAt->modify('+' . rand(1, 48) . ' hours');
                        $annonce->setPublishedAt($publishedAt);
                    }

                    // Vues aléatoires basées sur l'âge de l'annonce
                    $vuesBase = max(1, 100 - $joursEcoules);
                    $annonce->setVues(rand(max(0, $vuesBase - 50), $vuesBase + 200));

                    // Urgence occasionnelle
                    $annonce->setIsUrgent(rand(1, 15) === 1);

                    // Ajouter des photos si disponibles
                    if (isset($annonceInfo[3]) && !empty($annonceInfo[3])) {
                        // Utiliser directement les noms de fichiers
                        $annonce->setImages($annonceInfo[3]);
                    } else {
                        // Si pas d'images définies, ajouter une image placeholder avec nom de fichier
                        $seed = abs(crc32($titre));
                        $placeholderName = "placeholder-{$seed}.jpg";
                        $annonce->setImages([$placeholderName]);
                    }

                    $manager->persist($annonce);
                    $nombreAnnoncesCrees++;
                    $annoncesParCategorie[$nomCategorie]++;
                }
            }
        }

        // Étape 2 : Compléter avec les annonces restantes de manière aléatoire
        if ($nombreAnnoncesCrees < $maxAnnonces) {
            // Créer une liste plate de toutes les annonces disponibles avec leur catégorie
            $toutesLesAnnonces = [];
            foreach ($categories as $categorie) {
                $nomCategorie = $categorie->getNom();
                if (isset($annonceData[$nomCategorie])) {
                    foreach ($annonceData[$nomCategorie] as $annonceInfo) {
                        $toutesLesAnnonces[] = [$annonceInfo, $categorie];
                    }
                }
            }

            // Mélanger toutes les annonces
            shuffle($toutesLesAnnonces);

            // Compléter jusqu'à 60 annonces
            for ($i = 0; $i < count($toutesLesAnnonces) && $nombreAnnoncesCrees < $maxAnnonces; $i++) {
                $annonceAvecCategorie = $toutesLesAnnonces[$i];
                $annonceInfo = $annonceAvecCategorie[0];
                $categorie = $annonceAvecCategorie[1];

                $user = $this->getRandomUser($manager);

                $annonce = new Annonce();
                // Modifier légèrement le titre pour éviter les doublons
                $suffixes = [' - Bon état', ' - Vente rapide', ' - Négociable', ' - Comme neuf', ' - Occasion'];
                $titre = $annonceInfo[0] . $suffixes[array_rand($suffixes)];
                $annonce->setTitre($titre);
                $annonce->setDescription($annonceInfo[1]);

                // Varier légèrement le prix
                $prixBase = $annonceInfo[2];
                $variation = rand(-20, 20);
                $nouveauPrix = max(1, $prixBase + $variation);
                $annonce->setPrix($nouveauPrix);

                $annonce->setType(Annonce::TYPE_SELL);
                $annonce->setStatus(rand(0, 10) > 2 ? Annonce::STATUS_PUBLISHED : Annonce::STATUS_PENDING);
                $annonce->setUser($user);
                $annonce->setCategorie($categorie);

                // Localisation variée
                $villes = ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille'];
                $ville = $villes[array_rand($villes)];
                $codePostal = $this->getCodePostalForVille($ville);

                $annonce->setVille($ville);
                $annonce->setCodePostal($codePostal);
                $annonce->setLocalisation($ville . ', France');

                // Dates de création variées
                $joursEcoules = rand(1, 90);
                $createdAt = new \DateTime('-' . $joursEcoules . ' days');
                $annonce->setCreatedAt($createdAt);

                if ($annonce->getStatus() === Annonce::STATUS_PUBLISHED) {
                    $publishedAt = clone $createdAt;
                    $publishedAt->modify('+' . rand(1, 48) . ' hours');
                    $annonce->setPublishedAt($publishedAt);
                }

                // Vues aléatoires basées sur l'âge de l'annonce
                $vuesBase = max(1, 100 - $joursEcoules);
                $annonce->setVues(rand(max(0, $vuesBase - 50), $vuesBase + 200));

                // Urgence occasionnelle
                $annonce->setIsUrgent(rand(1, 15) === 1);

                // Ajouter des photos si disponibles
                if (isset($annonceInfo[3]) && !empty($annonceInfo[3])) {
                    // Utiliser directement les noms de fichiers
                    $annonce->setImages($annonceInfo[3]);
                } else {
                    // Si pas d'images définies, ajouter une image placeholder avec nom de fichier
                    $seed = abs(crc32($titre));
                    $placeholderName = "placeholder-{$seed}.jpg";
                    $annonce->setImages([$placeholderName]);
                }

                $manager->persist($annonce);
                $nombreAnnoncesCrees++;
                $annoncesParCategorie[$categorie->getNom()]++;
            }
        }

        // Résumé de création des annonces
        // {$nombreAnnoncesCrees} annonces créées
        // Répartition disponible dans $annoncesParCategorie

        $manager->flush();
    }

    private function getCodePostalForVille(string $ville): string
    {
        $codesPostaux = [
            'Paris' => ['75001', '75002', '75003', '75004', '75005', '75015', '75016', '75017'],
            'Lyon' => ['69001', '69002', '69003', '69006', '69007'],
            'Marseille' => ['13001', '13002', '13006', '13008'],
            'Toulouse' => ['31000', '31100', '31200', '31300'],
            'Nice' => ['06000', '06100', '06200'],
            'Nantes' => ['44000', '44100', '44200'],
            'Montpellier' => ['34000', '34070', '34080'],
            'Strasbourg' => ['67000', '67100', '67200'],
            'Bordeaux' => ['33000', '33100', '33200'],
            'Lille' => ['59000', '59100', '59800']
        ];

        $codes = $codesPostaux[$ville] ?? ['75000'];
        return $codes[array_rand($codes)];
    }

    private function getRandomUser(ObjectManager $manager): User
    {
        // Créer des utilisateurs variés à la demande
        if (count($this->users) < 20) {
            $prenoms = ['Sophie', 'Pierre', 'Marie', 'Paul', 'Julie', 'Thomas', 'Emma', 'Lucas', 'Sarah', 'Nicolas', 'Camille', 'Antoine', 'Léa', 'Julien', 'Chloe', 'Alexandre', 'Manon', 'Maxime', 'Clara', 'Hugo'];
            $noms = ['Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit', 'Durand', 'Leroy', 'Moreau', 'Simon', 'Laurent', 'Lefebvre', 'Michel', 'Garcia', 'Roux', 'David', 'Bertrand', 'Morel', 'Fournier'];

            $user = $this->createRandomUser($manager, $prenoms, $noms);
            $this->users[] = $user;
            return $user;
        }

        return $this->users[array_rand($this->users)];
    }

    private function createRandomUser(ObjectManager $manager, array $prenoms, array $noms): User
    {
        $this->userCounter++;

        $user = new User();
        $user->setEmail("user{$this->userCounter}@example.com");
        $user->setFirstName($prenoms[array_rand($prenoms)]);
        $user->setLastName($noms[array_rand($noms)]);
        $user->setIsVerified(true);
        $user->setCreatedAt(new \DateTime('-' . rand(30, 365) . ' days'));

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'TempPass123!');
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        return $user;
    }

    public function getDependencies(): array
    {
        return [
            CategorieFixtures::class,
        ];
    }
}
