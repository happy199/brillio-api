#!/bin/bash

# Script d'initialisation de la VM GCP pour Brillio
# À exécuter une seule fois lors de la création de la VM

set -e

echo "🚀 Initialisation de la VM Brillio..."

# Mise à jour du système
echo "📦 Mise à jour du système..."
sudo apt-get update
sudo apt-get upgrade -y

# Installation de Docker
echo "🐳 Installation de Docker..."
sudo apt-get install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Ajouter l'utilisateur au groupe docker
sudo usermod -aG docker $USER

# Installation de Docker Compose standalone
echo "📦 Installation de Docker Compose..."
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Créer le répertoire de l'application
echo "📁 Création du répertoire de l'application..."
sudo mkdir -p /opt/brillio-api
sudo chown -R $USER:$USER /opt/brillio-api

# Configuration du firewall
echo "🔥 Configuration du firewall..."
sudo apt-get install -y ufw
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw --force enable

# Installation de fail2ban pour la sécurité SSH
echo "🔒 Installation de fail2ban..."
sudo apt-get install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Créer le fichier .env
echo "📝 Création du fichier .env..."
cat > /opt/brillio-api/.env << 'EOF'
APP_ENV=production
APP_DEBUG=false
APP_KEY=

DB_ROOT_PASSWORD=
DB_DATABASE=brillioapi
DB_USERNAME=brillio
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
EOF

echo "✅ Initialisation terminée !"
echo ""
echo "📋 Prochaines étapes :"
echo "1. Générer APP_KEY : cd /opt/brillio-api && docker run --rm -v \$(pwd):/app composer:latest create-project laravel/laravel temp && cat temp/.env | grep APP_KEY"
echo "2. Éditer /opt/brillio-api/.env et remplir les mots de passe"
echo "3. Configurer les secrets GitHub (VM_IP, VM_USER, VM_SSH_KEY)"
echo "4. Déployer via GitHub Actions"
echo ""
echo "⚠️  IMPORTANT : Déconnectez-vous et reconnectez-vous pour que les changements de groupe Docker prennent effet"
