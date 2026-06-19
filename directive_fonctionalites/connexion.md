├── .env
│   └── Fichier de configuration de l'environnement.
│       On y déclare le domaine du front Vue (SANCTUM_STATEFUL_DOMAINS),
│       le driver de session (SESSION_DRIVER=cookie) et l'URL du front
│       (FRONTEND_URL). Sans ces valeurs, Sanctum ne sait pas que les
│       requêtes venant de Vue.js sont légitimes.
│
├── config/sanctum.php
│   └── Configuration de Sanctum lui-même.
│       On y définit la liste des domaines "stateful" (domaines autorisés
│       à utiliser les cookies de session). C'est ici que Sanctum vérifie
│       si une requête vient d'un domaine de confiance avant de valider
│       la session.
│
├── config/cors.php
│   └── Configuration CORS (Cross-Origin Resource Sharing).
│       Sans ce fichier, le navigateur bloque toutes les requêtes
│       venant de Vue.js (localhost:5173) vers Laravel (localhost:8000)
│       car ce sont deux origines différentes. On y autorise le front,
│       et on active supports_credentials:true pour que les cookies
│       de session passent avec chaque requête.
│
├── app/Models/User.php
│   └── Modèle Eloquent représentant la table "users" en base de données.
│       On y ajoute le trait HasApiTokens (fourni par Sanctum) qui donne
│       à chaque utilisateur la capacité d'avoir une session/token.
│       On y déclare aussi les colonnes autorisées (fillable), les colonnes
│       cachées (password), et la relation vers le modèle Role.
│
├── app/Http/Controllers/Api/AuthController.php
│   └── Contrôleur central de l'authentification.
│       Contient 4 méthodes :
│       - register() : crée le compte, connecte automatiquement, retourne l'user
│       - login()    : vérifie email+mot de passe, vérifie is_active, crée la session
│       - logout()   : détruit la session côté serveur
│       - me()       : retourne l'utilisateur de la session active (utile au
│                      rechargement de page pour savoir si on est connecté)
│
└── routes/api.php
    └── Fichier de déclaration de toutes les routes API Laravel.
        On y branche les 4 méthodes du AuthController sur des URLs précises :
        - POST /api/register  → public
        - POST /api/login     → public
        - POST /api/logout    → protégé (middleware auth:sanctum)
        - GET  /api/me        → protégé (middleware auth:sanctum)
        Le middleware auth:sanctum sur le groupe protégé signifie que Laravel
        vérifie automatiquement la session/cookie avant de laisser passer.



FRONT-END Vue.js
├── src/api/axios.js
│   └── Instance Axios partagée par tout le projet.
│       On y configure l'URL de base (baseURL: localhost:8000) et surtout
│       withCredentials:true, qui est le réglage critique permettant au
│       navigateur d'envoyer les cookies de session avec chaque requête.
│       Sans withCredentials, Laravel reçoit les requêtes mais ne reconnaît
│       jamais l'utilisateur connecté.
│
├── src/api/authApi.js
│   └── Couche d'accès aux routes d'authentification Laravel.
│       Contient des fonctions propres et nommées pour chaque appel :
│       getCsrfCookie(), register(), login(), logout(), me().
│       Séparer les appels API dans ce fichier évite de mélanger la logique
│       réseau avec la logique métier du store.
│
├── src/store/authStore.js
│   └── Store Pinia = mémoire centrale de l'authentification côté Vue.
│       Stocke l'utilisateur connecté (user), l'état de chargement (loading)
│       et les erreurs de validation (errors).
│       Expose les actions register(), login(), logout(), fetchUser() qui
│       appellent authApi.js et mettent à jour l'état en conséquence.
│       Expose les getters isAuthenticated et userRole utilisés partout
│       dans l'appli pour afficher ou cacher des éléments selon le rôle.
│
├── src/views/auth/RegisterView.vue
│   └── Page du formulaire d'inscription (nom, email, mot de passe,
│       confirmation). Liée au store via useAuthStore(). Au submit,
│       appelle authStore.register(form). Affiche les erreurs de
│       validation retournées par Laravel champ par champ.
│
├── src/views/auth/LoginView.vue
│   └── Page du formulaire de connexion (email, mot de passe).
│       Liée au store via useAuthStore(). Au submit, appelle
│       authStore.login(form). Affiche les erreurs Laravel.
│       Après connexion réussie, le store redirige automatiquement
│       vers le bon espace selon le rôle (admin, gestionnaire, rh, user).
│
├── src/router/index.js
│   └── Fichier de déclaration de toutes les routes Vue (pages).
│       Chaque route peut avoir des métadonnées :
│       - meta.requiresAuth:true  → route accessible uniquement si connecté
│       - meta.role:'admin'       → accessible uniquement à ce rôle précis
│       Contient le guard de navigation (beforeEach) qui s'exécute avant
│       chaque changement de page pour vérifier si l'utilisateur a le droit
│       d'accéder à la route demandée. Si non connecté → redirige /connexion.
│       Si mauvais rôle → redirige vers l'accueil.
│
├── src/main.js
│   └── Point d'entrée de l'application Vue.
│       On y initialise Pinia (gestionnaire d'état) et Vue Router,
│       puis on monte l'application sur le DOM. C'est ici qu'on branche
│       tous les plugins ensemble avant le démarrage.
│
└── src/App.vue
    └── Composant racine de l'application (parent de toutes les pages).
        Contient un onMounted() qui appelle authStore.fetchUser() au
        démarrage. Cela permet de récupérer l'utilisateur depuis la session
        Laravel active même après un rechargement de page, évitant ainsi
        d'être déconnecté à chaque F5.



En résumé la chaîne complète est :
App.vue (démarrage)
  └→ authStore.fetchUser()
       └→ authApi.me()
            └→ GET /api/me (Laravel vérifie le cookie)
                 └→ retourne l'user → stocké dans authStore.user

LoginView.vue (soumission formulaire)
  └→ authStore.login()
       └→ authApi.getCsrfCookie()  ← étape obligatoire Sanctum
       └→ authApi.login()
            └→ POST /api/login (Laravel crée la session, pose le cookie)
                 └→ retourne l'user → stocké dans authStore.user
                      └→ router redirige selon le rôle