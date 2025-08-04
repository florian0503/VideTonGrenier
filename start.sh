#!/bin/bash

echo "🏠 Démarrage de l'environnement VideTonGrenier..."

# Démarrer Docker Compose (version simplifiée)
echo "📦 Démarrage des services Docker..."
docker-compose -f docker-compose.simple.yml up -d

echo "⏳ Attendre que la base de données soit prête..."
sleep 15

# Créer les migrations et appliquer
echo "🗄️  Création et application des migrations..."
php bin/console doctrine:migration:diff --no-interaction || true
php bin/console doctrine:migration:migrate --no-interaction

# Compiler les assets
echo "🎨 Compilation des assets..."
npm run build

# Démarrer le serveur PHP
echo "🚀 Démarrage du serveur de développement..."
php -S 0.0.0.0:8001 -t public/ &
SERVER_PID=$!

echo ""
echo "✅ Environnement VideTonGrenier prêt!"
echo "🌐 Site accessible sur: http://localhost:8001"
echo "🗄️  Base de données MySQL sur: localhost:3306"
echo "📧 Interface Mailpit: http://localhost:8025"
echo ""
echo "Commandes utiles:"
echo "  docker-compose -f docker-compose.simple.yml logs database  # Logs MySQL"
echo "  docker-compose -f docker-compose.simple.yml down           # Arrêter Docker"
echo "  npm run watch                                              # Mode dev assets"
echo "  kill $SERVER_PID                                          # Arrêter le serveur"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter..."

# Attendre l'interruption
trap "kill $SERVER_PID; docker-compose -f docker-compose.simple.yml down; exit" INT
wait