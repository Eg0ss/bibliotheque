# Guide de déploiement local (Laravel + Vue.js)

Suivez ces étapes dans l'ordre pour installer et lancer le projet sur votre machine.

## 1. Clonage du projet
Téléchargez le dépôt et placez-vous dans le dossier du projet :
```bash
git clone <URL_DU_DEPOT_GITHUB>
cd <NOM_DU_DOSSIER>
```

## 2. Configuration du Backend (Laravel)
Installez les dépendances PHP, créez le fichier d'environnement et générez la clé de sécurité :
```bash
composer install
cp .env.example .env
php artisan key:generate
```
> 💡 *Note : Ouvrez le fichier `.env` pour configurer les accès à votre base de données (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*

## 3. Configuration du Frontend (Vue.js)
Installez les modules JavaScript :
```bash
npm install
```

## 4. Base de données
Exécutez les migrations pour créer les tables (et ajoutez les données de test si disponibles) :
```bash
php artisan migrate --seed
```

## 5. Lancement de l'application
Vous devez exécuter ces deux commandes dans **deux terminaux différents** :

* **Terminal 1 (Serveur API Laravel) :**
  ```bash
  php artisan serve
  ```
* **Terminal 2 (Serveur Vite/Vue) :**
  ```bash
  npm run dev
  ```
