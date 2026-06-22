# Documentation — Création d'utilisateur par l'Admin
## Bibliothèque Numérique — Laravel + Vue.js + Sanctum

---

## 1. Vue d'ensemble de la fonctionnalité

L'administrateur peut créer un nouveau compte utilisateur depuis son espace privé.
Le formulaire de création collecte : nom, email, mot de passe, confirmation et rôle.
Les données transitent du formulaire Vue.js → Pinia Store → Axios → Laravel API → Base de données.

---

## 2. Tables de base de données utilisées

### `roles`
| Colonne | Type | Rôle |
|--------|------|------|
| id | bigint PK | Identifiant unique |
| name | string | Libellé affiché ("Administrateur", "Gestionnaire"...) |
| slug | string UNIQUE | Identifiant technique ("admin", "gestionnaire", "rh", "user") |
| timestamps | datetime | created_at, updated_at |

**Utilisée pour** : remplir le `<select>` du formulaire de création, valider que le `role_id` soumis existe bien en base, afficher le badge de rôle dans la liste.

---

### `users`
| Colonne | Type | Rôle |
|--------|------|------|
| id | bigint PK | Identifiant unique |
| name | string | Nom complet |
| email | string UNIQUE | Adresse e-mail (identifiant de connexion) |
| password | string | Mot de passe hashé (bcrypt automatique via cast Laravel) |
| role_id | bigint FK → roles.id | Rôle attribué |
| is_active | boolean | Compte actif (true par défaut à la création) |
| email_verified_at | timestamp nullable | Vérification e-mail (non utilisée pour l'instant) |
| remember_token | string | Token "se souvenir de moi" |
| timestamps | datetime | created_at, updated_at |
| deleted_at | timestamp nullable | Suppression douce (SoftDeletes) |

**Utilisée pour** : stocker le nouveau compte, authentifier l'utilisateur, contrôler son accès selon son rôle.

---

### `personal_access_tokens`
| Colonne | Type | Rôle |
|--------|------|------|
| id | bigint PK | Identifiant |
| tokenable_type / tokenable_id | morphs | Relation polymorphique vers User |
| name | string | Nom du token |
| token | string UNIQUE | Hash du token Sanctum |
| abilities | text | Permissions du token |
| last_used_at | timestamp | Dernière utilisation |
| expires_at | timestamp | Expiration |

**Utilisée par** : Sanctum pour gérer les sessions/tokens d'authentification. Permet à Laravel de savoir qui est connecté à chaque requête API.

---

### `sessions`
| Colonne | Type | Rôle |
|--------|------|------|
| id | string PK | Identifiant de session |
| user_id | bigint nullable | Utilisateur lié |
| ip_address | string | IP du client |
| user_agent | text | Navigateur |
| payload | longText | Données de session sérialisées |
| last_activity | integer | Timestamp dernière activité |

**Utilisée par** : Sanctum en mode session-based (cookie). Maintient la session de l'admin connecté entre les requêtes.

---

## 3. Fichiers BACK-END (Laravel)

### `back/database/migrations/2026_06_19_145211_create_roles_table.php`
**Chemin** : `back/database/migrations/`
**Rôle** : Crée la table `roles` avec les colonnes `name` et `slug`.
**Logique** : Migration Laravel standard. Exécutée une fois avec `php artisan migrate`.

---

### `back/database/migrations/..._create_users_table.php`
**Chemin** : `back/database/migrations/`
**Rôle** : Crée la table `users` avec toutes ses colonnes dont `role_id` (FK vers roles) et `is_active`.
**Logique** : Définit la structure de stockage des comptes utilisateurs.

---

### `back/database/seeders/RoleSeeder.php`
**Chemin** : `back/database/seeders/`
**Rôle** : Pré-remplit la table `roles` avec les 4 rôles du projet.
**Logique** :
```php
// Insère les 4 rôles de base
Role::firstOrCreate(['slug' => 'admin'],        ['name' => 'Administrateur']);
Role::firstOrCreate(['slug' => 'gestionnaire'], ['name' => 'Gestionnaire']);
Role::firstOrCreate(['slug' => 'rh'],           ['name' => 'Ressources Humaines']);
Role::firstOrCreate(['slug' => 'user'],         ['name' => 'Utilisateur']);
```
**Commande** : `php artisan db:seed --class=RoleSeeder`

---

### `back/database/seeders/AdminUserSeeder.php`
**Chemin** : `back/database/seeders/`
**Rôle** : Crée le compte admin de test (`admin@bibliotheque.bj` / `password123`).
**Logique** : Récupère le rôle "admin" par slug, crée l'utilisateur si inexistant.
**Commande** : `php artisan db:seed --class=AdminUserSeeder`

---

### `back/app/Models/Role.php`
**Chemin** : `back/app/Models/`
**Rôle** : Modèle Eloquent représentant la table `roles`.
**Logique** :
```php
// Relation : un rôle a plusieurs utilisateurs
public function users() {
    return $this->hasMany(User::class);
}
```

---

### `back/app/Models/User.php`
**Chemin** : `back/app/Models/`
**Rôle** : Modèle Eloquent central. Représente un compte utilisateur.
**Logique clé** :
- `HasApiTokens` (Sanctum) : donne la capacité d'avoir des sessions/tokens
- `fillable` : colonnes autorisées à l'écriture en masse (sécurité)
- `hidden` : password et remember_token jamais exposés en JSON
- Cast `'password' => 'hashed'` : hashage automatique à l'écriture
- Cast `'is_active' => 'boolean'`
- Relation `role()` : `belongsTo(Role::class)` → accès à `$user->role->slug`

---

### `back/app/Http/Requests/User/StoreUserRequest.php`
**Chemin** : `back/app/Http/Requests/User/`
**Rôle** : Valide les données du formulaire de création AVANT qu'elles atteignent le contrôleur.
**Logique** :
```php
public function rules(): array {
    return [
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email', // email unique
        'password' => 'required|string|min:8|confirmed',   // confirmation requise
        'role_id'  => 'required|exists:roles,id',          // rôle doit exister en base
    ];
}
```
**Avantage** : Si la validation échoue, Laravel retourne automatiquement une réponse 422 avec les erreurs par champ — Vue les affiche directement sous chaque input.

---

### `back/app/Http/Resources/UserResource.php`
**Chemin** : `back/app/Http/Resources/`
**Rôle** : Formate proprement l'objet User en JSON avant de l'envoyer à Vue.
**Logique** : Contrôle exactement quels champs sont exposés (pas de password, created_at formaté en dd/mm/yyyy, rôle avec id + name + slug inclus via `whenLoaded`).

---

### `back/app/Http/Controllers/Api/UserController.php`
**Chemin** : `back/app/Http/Controllers/Api/`
**Rôle** : Contrôleur API qui gère toutes les opérations sur les utilisateurs.
**Méthodes implémentées à cette étape** :

| Méthode | Route | Action |
|---------|-------|--------|
| `index()` | GET /api/admin/users | Liste paginée (15/page) avec eager loading du rôle |
| `store()` | POST /api/admin/users | Crée l'utilisateur avec `is_active=true` par défaut |
| `show()` | GET /api/admin/users/{id} | Retourne un utilisateur via `findOrFail` (404 auto si absent) |
| `getRoles()` | GET /api/admin/roles | Retourne tous les rôles pour le `<select>` du formulaire |

**Logique de `store()`** :
```php
$user = User::create([
    'name'      => $request->validated()['name'],
    'email'     => $request->validated()['email'],
    'password'  => $request->validated()['password'], // hashé auto
    'role_id'   => $request->validated()['role_id'],
    'is_active' => true,
]);
$user->load('role'); // eager load pour la réponse JSON
return response()->json(['message' => '...', 'user' => new UserResource($user)], 201);
```

---

### `back/routes/api.php`
**Chemin** : `back/routes/`
**Rôle** : Déclare toutes les routes API et leur middleware.
**Logique** :
```php
// Routes publiques (sans auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Routes protégées : middleware auth:sanctum vérifie la session cookie
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::get('roles', [UserController::class, 'getRoles']); // pour le <select>
    });
});
```

---

### `back/config/cors.php`
**Chemin** : `back/config/`
**Rôle** : Autorise les requêtes cross-origin venant de Vue.js (localhost:5173 → localhost:8000).
**Logique clé** : `supports_credentials: true` permet aux cookies de session de passer avec chaque requête Axios.

---

### `back/config/sanctum.php`
**Chemin** : `back/config/`
**Rôle** : Configure les domaines "stateful" de Sanctum.
**Logique** : Déclare `localhost:5173` comme domaine de confiance → Sanctum accepte ses cookies de session.

---

### `back/.env`
**Chemin** : `back/`
**Rôle** : Variables d'environnement de l'application.
**Variables critiques ajoutées** :
```env
SANCTUM_STATEFUL_DOMAINS=localhost:5173
SESSION_DRIVER=cookie
FRONTEND_URL=http://localhost:5173
```

---

## 4. Fichiers FRONT-END (Vue.js + Pinia)

### `front/src/main.js`
**Chemin** : `front/src/`
**Rôle** : Point d'entrée de l'application Vue. Initialise et branche tous les plugins.
**Logique** : Crée l'app Vue, installe Pinia (état global) et Vue Router, monte sur `#app`.

---

### `front/src/App.vue`
**Chemin** : `front/src/`
**Rôle** : Composant racine. Choisit dynamiquement le bon layout selon `route.meta.layout`.
**Logique clé** :
```js
// Sélection du layout selon la route active
const currentLayout = computed(() => {
    const layout = route.meta.layout || 'guest'
    if (layout === 'none')  return null         // Login : plein écran
    if (layout === 'admin') return AdminLayout  // Sidebar back-office
    if (layout === 'user')  return UserLayout   // Layout user connecté
    return GuestLayout                          // Pages publiques
})
```
**onMounted** : appelle `authStore.fetchUser()` pour restaurer la session après F5.

---

### `front/src/layouts/AdminLayout.vue`
**Chemin** : `front/src/layouts/`
**Rôle** : Layout partagé par Admin, Gestionnaire et RH. Affiche la sidebar de navigation.
**Logique clé** :
- `sidebarItems` est un `computed` qui retourne les menus selon `authStore.userRole`
- Admin → Tableau de bord, Utilisateurs, Publications, Assignations, Décisions finales
- Gestionnaire → ses propres menus
- RH → ses propres menus
- Contient `<RouterView />` dans le `<main>` pour afficher la page active

**Bug corrigé** : `authStore.role` → `authStore.userRole` (le getter Pinia correct).

---

### `front/src/router/index.js`
**Chemin** : `front/src/router/`
**Rôle** : Déclare toutes les routes Vue et protège les accès par rôle.
**Logique des meta** :
```js
// Chaque route a des métadonnées
meta: {
    layout: 'admin',       // → App.vue choisit AdminLayout
    requiresAuth: true,    // → guard vérifie la session
    role: 'admin'          // → guard vérifie le rôle exact
}
```
**Guard `beforeEach`** :
1. Si `requiresAuth` → appelle `fetchUser()` si non authentifié
2. Si toujours non authentifié → redirige `/connexion`
3. Si mauvais rôle → redirige `/`
4. Si déjà connecté sur `/connexion` → redirige `/`

**Routes admin créées** :
- `/admin` → DashboardView
- `/admin/utilisateurs` → UsersListView
- `/admin/utilisateurs/nouveau` → UserCreateView
- `/admin/utilisateurs/:id` → UserShowView

---

### `front/src/api/axios.js`
**Chemin** : `front/src/api/`
**Rôle** : Instance Axios unique, partagée par tous les fichiers `*Api.js`.
**Logique clé** :
```js
const apiClient = axios.create({
    baseURL: 'http://localhost:8000',
    withCredentials: true, // CRUCIAL : envoie les cookies de session avec chaque requête
    headers: { 'Accept': 'application/json' }
})
```

---

### `front/src/api/authApi.js`
**Chemin** : `front/src/api/`
**Rôle** : Fonctions d'appel aux routes d'authentification Laravel.
**Fonctions** : `getCsrfCookie()`, `register()`, `login()`, `logout()`, `me()`
**Point critique** : `getCsrfCookie()` appelle `GET /sanctum/csrf-cookie` avant chaque POST — obligatoire avec Sanctum pour la protection CSRF.

---

### `front/src/api/userApi.js`
**Chemin** : `front/src/api/`
**Rôle** : Fonctions d'appel aux routes admin/users de Laravel.
**Fonctions utilisées à cette étape** :
```js
getAll(page)   → GET  /api/admin/users?page=N
getOne(id)     → GET  /api/admin/users/{id}
create(data)   → POST /api/admin/users
getRoles()     → GET  /api/admin/roles
```

---

### `front/src/stores/authStore.js`
**Chemin** : `front/src/stores/`
**Rôle** : Store Pinia central de l'authentification. Mémoire de l'utilisateur connecté.
**État** : `user` (objet user), `loading`, `errors`
**Getters** :
- `isAuthenticated` → `!!user.value`
- `userRole` → `user.value?.role?.slug` (retourne "admin", "gestionnaire"...)
**Actions** : `register()`, `login()`, `logout()`, `fetchUser()`, `redirectAfterLogin()`

**Logique `redirectAfterLogin()`** :
```js
if (role === 'admin')        router.push('/admin')
else if (role === 'gestionnaire') router.push('/gestionnaire')
else if (role === 'rh')      router.push('/rh')
else                         router.push('/mon-espace/depots')
```

---

### `front/src/stores/userStore.js`
**Chemin** : `front/src/stores/`
**Rôle** : Store Pinia dédié à la gestion des utilisateurs (CRUD).
**État** : `users[]`, `pagination`, `roles[]`, `loading`, `errors`, `success`
**Actions** :
- `fetchUsers(page)` → charge la liste paginée, stocke dans `users` et `pagination`
- `fetchRoles()` → charge les rôles pour le `<select>` du formulaire
- `createUser(formData)` → envoie le formulaire, gère les erreurs 422 (validation), redirige vers la liste après succès

---

### `front/src/views/auth/LoginView.vue`
**Chemin** : `front/src/views/auth/`
**Rôle** : Formulaire de connexion (email + mot de passe).
**Logique** : `v-model` sur les champs → `authStore.login(form)` au submit → redirection automatique selon le rôle via `redirectAfterLogin()`.

---

### `front/src/views/admin/DashboardView.vue`
**Chemin** : `front/src/views/admin/`
**Rôle** : Page d'accueil de l'espace admin. Cards de statistiques + accès rapides.
**Route** : `/admin` (index du groupe admin)

---

### `front/src/views/admin/UsersListView.vue`
**Chemin** : `front/src/views/admin/`
**Rôle** : Liste paginée de tous les utilisateurs avec badges rôle et statut.
**Logique** :
- `onMounted` → `userStore.fetchUsers()`
- Tableau avec colonnes : Nom, Email, Rôle (badge coloré), Statut (actif/inactif), Date création, lien "Voir"
- Pagination avec boutons Précédent/Suivant liés à `pagination.current_page` et `pagination.last_page`
- Bouton "+ Créer un compte" → `router-link` vers `/admin/utilisateurs/nouveau`

**Badges rôles** :
- admin → rouge
- gestionnaire → bleu
- rh → violet
- user → gris

---

### `front/src/views/admin/UserCreateView.vue`
**Chemin** : `front/src/views/admin/`
**Rôle** : Formulaire de création d'un compte utilisateur par l'admin.
**Logique** :
1. `onMounted` → `userStore.fetchRoles()` pour peupler le `<select>`
2. `form` reactive avec : name, email, password, password_confirmation, role_id
3. `handleSubmit()` → `userStore.createUser(form)`
4. Affichage des erreurs Laravel champ par champ (sous chaque input)
5. Affichage du message de succès (ou Toast PrimeVue)
6. Redirection automatique vers `/admin/utilisateurs` après succès

---

## 5. Flux complet de la création d'un utilisateur

```
[Admin remplit le formulaire UserCreateView.vue]
         │
         ▼
[handleSubmit() → userStore.createUser(form)]
         │
         ▼
[userApi.create(data) → POST /api/admin/users]
         │  (Axios envoie le cookie de session Sanctum)
         ▼
[Laravel : middleware auth:sanctum vérifie la session]
         │
         ▼
[StoreUserRequest valide les données]
         │  Échec → 422 + { errors: { email: [...], ... } }
         │  Succès → continue
         ▼
[UserController::store() crée User::create([...])]
         │  (password hashé auto, is_active=true)
         ▼
[UserResource formate la réponse JSON]
         │
         ▼
[Vue reçoit 201 → userStore.success = 'Compte créé']
         │
         ▼
[Toast PrimeVue affiché + redirect /admin/utilisateurs]
```

---

## 6. Bugs rencontrés et corrections

| Bug | Cause | Correction |
|-----|-------|-----------|
| Sidebar admin jamais affichée | `App.vue` affichait `<NavBar>` + `<RouterView>` pour tout le monde sans layout conditionnel | `App.vue` utilise `computed` pour choisir `AdminLayout`, `UserLayout` ou `GuestLayout` selon `route.meta.layout` |
| Guard bloquait l'accès à `/admin` | `userRole` comparait `role.name` ("Administrateur") avec `"admin"` | Changé `role.name` → `role.slug` dans `authStore.js` |
| `next()` deprecated dans le guard | Ancienne syntaxe Vue Router 3 | Remplacé `next('/chemin')` par `return '/chemin'` |
| Formulaire sans rôles dans le `<select>` | Route `GET /api/admin/roles` manquante | Ajout de la route + méthode `getRoles()` dans `UserController` |
