# Déploiement de BuildFlow sur buildflow.materconstruct.com

---

## 1. Prérequis serveur

Avant de commencer, le serveur doit disposer de :

| Élément      | Version minimale |
|--------------|-----------------|
| PHP          | 8.2+            |
| MySQL        | 8.0+            |
| Redis        | 7+              |
| Nginx        | 1.18+           |
| Composer     | 2.x             |
| Node.js      | 18+             |
| npm          | 9+              |

Extensions PHP requises : `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` ou `imagick`, `zip`, `curl`.

---

## 2. Fichiers et dossiers à envoyer

### ✅ À envoyer (via Git ou FTP/rsync)

```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
  app/
  framework/
    cache/
    sessions/
    views/
  logs/           ← dossier vide, ne pas envoyer les logs
artisan
composer.json
composer.lock
package.json
package-lock.json
vite.config.js
tailwind.config.js
postcss.config.js
phpunit.xml
.env.example      ← renommer en .env sur le serveur et configurer
```

### ❌ Ne PAS envoyer

```
vendor/           ← généré par "composer install" sur le serveur
node_modules/     ← généré par "npm install" sur le serveur
public/build/     ← généré par "npm run build" sur le serveur
public/storage    ← lien symbolique créé par "artisan storage:link"
.env              ← contient les secrets, à créer manuellement
storage/logs/*.log
bootstrap/cache/*.php
.git/
template_model/
documents/
docker/
docker-compose.yml
```

---

## 3. Méthode recommandée : déploiement via Git

### 3.1 — Connexion au serveur

```bash
ssh utilisateur@materconstruct.com
```

### 3.2 — Cloner le dépôt dans le bon répertoire

```bash
cd /var/www
git clone https://github.com/<votre-repo>/buildflow.git buildflow
cd buildflow
```

> Si pas de dépôt Git, envoyer les fichiers avec rsync :
> ```bash
> rsync -avz --exclude=vendor --exclude=node_modules --exclude=public/build \
>   --exclude=.env --exclude=".git" \
>   /chemin/local/BuildFlow/ utilisateur@materconstruct.com:/var/www/buildflow/
> ```

---

## 4. Installation des dépendances sur le serveur

```bash
# Installer les dépendances PHP (sans les paquets de développement)
composer install --no-dev --optimize-autoloader

# Installer les dépendances JS et compiler les assets
npm install
npm run build
```

---

## 5. Configuration du fichier .env

```bash
cp .env.example .env
nano .env
```

Modifier les variables suivantes :

```dotenv
APP_NAME=BuildFlow
APP_ENV=production
APP_KEY=                        # sera généré à l'étape suivante
APP_DEBUG=false
APP_URL=https://buildflow.materconstruct.com

APP_TIMEZONE=Africa/Algiers     # ou votre fuseau horaire

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=buildflow_db
DB_USERNAME=buildflow_user
DB_PASSWORD=VotreMotDePasse

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@materconstruct.com
MAIL_FROM_NAME=BuildFlow
```

---

## 6. Commandes d'initialisation Laravel

```bash
# Générer la clé d'application
php artisan key:generate

# Créer le lien symbolique pour le stockage public
php artisan storage:link

# Exécuter les migrations
php artisan migrate --force

# (Optionnel) Exécuter les seeders si nécessaire
php artisan db:seed --force

# Mettre en cache la configuration pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 7. Permissions des dossiers

```bash
# Donner les droits à Nginx/PHP-FPM (généralement www-data)
chown -R www-data:www-data /var/www/buildflow
chmod -R 755 /var/www/buildflow
chmod -R 775 /var/www/buildflow/storage
chmod -R 775 /var/www/buildflow/bootstrap/cache
```

---

## 8. Configuration DNS

Dans le panneau de gestion DNS de `materconstruct.com`, ajouter un enregistrement :

| Type  | Nom        | Valeur              | TTL  |
|-------|------------|---------------------|------|
| A     | buildflow  | IP_du_serveur       | 3600 |

> Remplacer `IP_du_serveur` par l'adresse IPv4 de votre serveur.

---

## 9. Configuration Nginx (virtual host)

Créer le fichier de configuration :

```bash
nano /etc/nginx/sites-available/buildflow.materconstruct.com
```

Contenu du fichier :

```nginx
server {
    listen 80;
    server_name buildflow.materconstruct.com;

    root /var/www/buildflow/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 50M;
}
```

Activer le site :

```bash
ln -s /etc/nginx/sites-available/buildflow.materconstruct.com \
      /etc/nginx/sites-enabled/

nginx -t                   # vérifier la syntaxe
systemctl reload nginx
```

---

## 10. Certificat SSL (HTTPS) avec Let's Encrypt

```bash
apt install certbot python3-certbot-nginx -y

certbot --nginx -d buildflow.materconstruct.com

# Renouvellement automatique (déjà configuré par certbot, vérifier avec :)
certbot renew --dry-run
```

Après cette étape, Nginx sera automatiquement mis à jour pour écouter sur le port 443 avec redirection HTTP → HTTPS.

---

## 11. Base de données — Création de l'utilisateur MySQL

```bash
mysql -u root -p
```

```sql
CREATE DATABASE buildflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'buildflow_user'@'localhost' IDENTIFIED BY 'VotreMotDePasse';
GRANT ALL PRIVILEGES ON buildflow_db.* TO 'buildflow_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 12. Mise à jour de l'application (déploiements suivants)

```bash
cd /var/www/buildflow

git pull origin main

composer install --no-dev --optimize-autoloader
npm install
npm run build

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Si des queues tournent avec Supervisor :
php artisan queue:restart
```

---

## 13. Workflow : pousser les changements locaux vers le serveur

Ce workflow couvre le cycle complet : modification en local → commit → push → mise à jour en production.

### 13.1 — En local : préparer et envoyer les changements

```bash
# Vérifier les fichiers modifiés
git status

# Ajouter tous les changements (ou fichier par fichier)
git add .

# Commit avec un message descriptif
git commit -m "feat: description du changement"

# Pousser vers le dépôt distant (branche main)
git push origin main
```

### 13.2 — Sur le serveur : récupérer et appliquer les changements

```bash
ssh utilisateur@materconstruct.com
cd /var/www/buildflow

# Récupérer les dernières modifications
git pull origin main
```

### 13.3 — Selon le type de changement, exécuter les commandes nécessaires

#### Si des fichiers PHP ont changé (Controllers, Models, routes…)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Si le fichier composer.json a changé (nouvelles dépendances PHP)
```bash
composer install --no-dev --optimize-autoloader
```

#### Si des fichiers JS/CSS ont changé (resources/js, resources/css, views Blade)
```bash
npm install          # seulement si package.json a changé
npm run build
```

#### Si de nouvelles migrations ont été ajoutées
```bash
php artisan migrate --force
```

#### Si le fichier .env.example a changé (nouvelles variables)
```bash
# Vérifier manuellement les nouvelles variables et les ajouter dans .env
nano .env
php artisan config:cache
```

#### Si des fichiers de stockage ou images sont concernés
```bash
php artisan storage:link
chown -R www-data:www-data /var/www/buildflow/storage
```

### 13.4 — Vider les caches après toute mise à jour importante

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Puis reconstruire les caches pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 13.5 — Script de déploiement automatisé (optionnel)

Créer un script `deploy.sh` à la racine du projet sur le serveur :

```bash
nano /var/www/buildflow/deploy.sh
```

```bash
#!/bin/bash
set -e

echo "==> Pulling latest changes..."
git pull origin main

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Building assets..."
npm install
npm run build

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Clearing and rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache

echo "==> Restarting queues..."
php artisan queue:restart

echo "✅ Déploiement terminé avec succès !"
```

Rendre le script exécutable :

```bash
chmod +x /var/www/buildflow/deploy.sh
```

Lancer la mise à jour en une seule commande :

```bash
cd /var/www/buildflow && bash deploy.sh
```

---

## 14. Récapitulatif rapide

```bash
# 1. Cloner / mettre à jour les fichiers
# 2. composer install --no-dev --optimize-autoloader
# 3. npm install && npm run build
# 4. Configurer .env
# 5. php artisan key:generate
# 6. php artisan storage:link
# 7. php artisan migrate --force
# 8. php artisan config:cache && route:cache && view:cache
# 9. chown -R www-data:www-data storage bootstrap/cache
# 10. Configurer Nginx + SSL
```
