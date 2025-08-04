# VideTonGrenier 🏠

Une plateforme moderne de petites annonces développée avec Symfony, inspirée du Bon Coin.

## 🚀 Fonctionnalités

### ✅ Implémentées
- **Architecture Symfony 7** avec structure MVC propre
- **Système d'authentification** complet (inscription, connexion, rôles)
- **Entités principales** : User, Annonce, Catégorie, Message
- **Design responsive** avec Bootstrap 5 et architecture Sass/BEM
- **Interface moderne** avec navigation intuitive
- **Environnement Docker** prêt pour le développement

### 🚧 En développement
- CRUD des annonces avec upload d'images
- Système de filtrage et recherche avancée
- Messagerie entre utilisateurs
- Back-office avec EasyAdmin
- Optimisations SEO (sitemap, robots.txt)

## 🛠 Stack Technique

- **Backend** : Symfony 7.3, PHP 8.3
- **Frontend** : Twig, Bootstrap 5, Sass avec méthodologie BEM
- **Build** : Webpack Encore
- **Base de données** : MySQL 8.0
- **Cache** : Redis
- **Containerisation** : Docker & Docker Compose

## 📦 Installation

### Prérequis
- Docker et Docker Compose
- Git

### Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone [URL_DU_REPO]
   cd VideTonGrenier
   ```

2. **Démarrer l'environnement**
   ```bash
   chmod +x start.sh
   ./start.sh
   ```

3. **Accéder à l'application**
   - Site web : http://localhost:8080
   - Base de données : localhost:3306

### Démarrage manuel

Si vous préférez démarrer manuellement :

```bash
# Démarrer les conteneurs
docker-compose up -d

# Attendre que la base soit prête puis créer les migrations
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate

# Compiler les assets
npm install
npm run build
```

## 🏗 Architecture

### Structure des entités
```
User (Utilisateur)
├── email, firstName, lastName
├── phone, address, city
├── roles, isVerified
└── Relations: annonces, sentMessages, receivedMessages

Annonce (Petite annonce)
├── titre, description, prix
├── type, status, localisation
├── images[], createdAt, publishedAt
└── Relations: user, categorie, messages

Categorie
├── nom, description, slug
├── icone, isActive
└── Relations: annonces

Message (Messagerie)
├── contenu, createdAt, isRead
└── Relations: sender, receiver, annonce
```

### Architecture Sass/BEM
```
assets/styles/
├── abstracts/     # Variables, mixins
├── base/          # Reset, typography
├── layouts/       # Container, grid
├── components/    # Composants BEM
└── app.scss       # Import principal
```

## 🎨 Design System

### Palette de couleurs
- **Primaire** : #007bff (Bleu)
- **Succès** : #28a745 (Vert)
- **Danger** : #dc3545 (Rouge)
- **Warning** : #ffc107 (Jaune)

### Méthodologie BEM
```scss
.block {}
.block__element {}
.block--modifier {}
```

## 🔧 Commandes utiles

### Développement
```bash
# Mode watch pour les assets
npm run watch

# Compilation production
npm run build

# Voir les logs Docker
docker-compose logs -f

# Arrêter l'environnement
docker-compose down
```

### Symfony
```bash
# Créer une entité
php bin/console make:entity

# Générer une migration
php bin/console doctrine:migration:diff

# Appliquer les migrations
php bin/console doctrine:migration:migrate

# Créer un contrôleur
php bin/console make:controller
```

## 📱 Responsive Design

L'interface s'adapte parfaitement à tous les écrans :
- **Mobile** : Navigation optimisée, cards empilées
- **Tablette** : Grille adaptative
- **Desktop** : Expérience complète

## 🔒 Sécurité

- Authentification Symfony Security
- Hashage des mots de passe avec bcrypt
- Protection CSRF sur les formulaires
- Validation des données côté serveur
- Échappement automatique des templates Twig

## 🚀 Déploiement

### Environnement de production
1. Configurer les variables d'environnement
2. Optimiser les assets : `npm run build`
3. Vider le cache : `php bin/console cache:clear --env=prod`
4. Configurer le serveur web (Nginx/Apache)

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature : `git checkout -b feature/ma-feature`
3. Commit : `git commit -am 'Ajout ma feature'`
4. Push : `git push origin feature/ma-feature`
5. Créer une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👨‍💻 Développement

Projet créé avec amour par l'équipe VideTonGrenier.

Pour toute question ou suggestion, n'hésitez pas à ouvrir une issue !