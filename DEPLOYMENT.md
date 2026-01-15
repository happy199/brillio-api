# 🚀 Déploiement VM GCP - Brillio API

## 💰 Nouvelle approche : VM unique avec Docker Compose

**Coût : $0-15/mois** (au lieu de $15-20/mois avec Cloud Run)

---

## 📦 Fichiers créés

### Configuration Docker
- ✅ `docker-compose.yml` - Orchestration app + MySQL
- ✅ `Dockerfile` - Image Laravel
- ✅ `.env.production.example` - Template variables
- ✅ `docker/mysql/my.cnf` - Config MySQL optimisée

### CI/CD Configuration
- ✅ `.github/workflows/deploy.yml` - Déploiement SSH

### Scripts
- ✅ `scripts/setup-vm.sh` - Initialisation VM
- ✅ `scripts/backup-mysql.sh` - Backup automatique

---

## 🎯 Étapes rapides

### 1. Créer la VM (5 min)

```bash
gcloud compute instances create brillio-vm \
  --zone=europe-west1-b \
  --machine-type=e2-micro \
  --image-family=debian-11 \
  --image-project=debian-cloud \
  --boot-disk-size=30GB \
  --tags=http-server,https-server
```

### 2. Configurer SSH (5 min)

```bash
# Générer clé
ssh-keygen -t rsa -b 4096 -f ~/.ssh/brillio-deploy

# Ajouter à GCP
gcloud compute instances add-metadata brillio-vm \
  --zone=europe-west1-b \
  --metadata ssh-keys="$USER:$(cat ~/.ssh/brillio-deploy.pub)"
```

### 3. Initialiser la VM (10 min)

```bash
# Se connecter
ssh -i ~/.ssh/brillio-deploy $USER@VM_IP

# Télécharger et exécuter le script
curl -o setup-vm.sh https://raw.githubusercontent.com/VOTRE_USERNAME/brillio-api/main/scripts/setup-vm.sh
chmod +x setup-vm.sh
./setup-vm.sh
```

### 4. Configurer GitHub Secrets (5 min)

Ajouter dans GitHub :
- `GCP_VM_IP` : IP de la VM
- `GCP_VM_USER` : Votre username
- `GCP_VM_SSH_KEY` : Contenu de `~/.ssh/brillio-deploy`

### 5. Déployer (5 min)

```bash
git add .
git commit -m "🚀 VM deployment"
git push origin main
```

---

## 📚 Documentation complète

Voir `gcp_deployment_guide.md` dans les artifacts pour le guide détaillé.

---

## 💡 Avantages

✅ **Gratuit** : VM e2-micro dans le Free Tier  
✅ **Simple** : Tout sur une machine  
✅ **Contrôle total** : Accès SSH complet  
✅ **Portable** : Docker standard  

---

## 🔍 Commandes utiles

```bash
# Se connecter
ssh -i ~/.ssh/brillio-deploy $USER@VM_IP

# Voir les logs
cd /opt/brillio-api && docker-compose logs -f

# Redémarrer
docker-compose restart

# Migrations
docker-compose exec app php artisan migrate
```
