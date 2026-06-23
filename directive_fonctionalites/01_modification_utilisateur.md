# Modification d'un utilisateur

> Fonctionnalité : l'admin peut modifier le nom, l'email, le rôle et le mot de passe d'un compte existant.

---

## Circuit complet de la donnée

```
[UserShowView.vue]        ← formulaire soumis par l'admin
      ↓ appelle
[userStore.js]            ← updateUser(id, formData)
      ↓ appelle
[userApi.js]              ← PUT /api/admin/users/{id}
      ↓ HTTP
[api.php]                 ← route : Route::apiResource('users', UserController::class)
      ↓ dirige vers
[UpdateUserRequest.php]   ← valide les données AVANT d'entrer dans le controller
      ↓ si valide
[UserController.php]      ← méthode update() modifie en base
      ↓ formate
[UserResource.php]        ← construit le JSON de réponse
      ↓ retourne
[userStore.js]            ← met à jour currentUser + affiche toast
      ↓ redirige
[UsersListView.vue]       ← retour à la liste
```

---

## Fichiers impliqués

### 1. `back/app/Http/Requests/User/UpdateUserRequest.php`

**Rôle :** Valider les données du formulaire **avant** qu'elles n'arrivent dans le controller. C'est un "filtre d'entrée" automatique de Laravel — si la validation échoue, Laravel renvoie directement une erreur 422 sans même appeler `update()`.

**Pourquoi un fichier séparé et pas dans le controller ?**
Parce que le controller doit rester concentré sur la logique métier. Mettre la validation dans un `FormRequest` dédié respecte le principe de responsabilité unique (chaque classe fait une seule chose).

**Ce qu'il fait ici :**
- `name` : obligatoire, entre 2 et 100 caractères
- `email` : obligatoire, format valide, unique dans `users` **sauf pour ce user lui-même** (grâce à `Rule::unique()->ignore($userId)`)
- `role_id` : obligatoire, doit exister dans la table `roles`
- `password` : **optionnel** — s'il est fourni, minimum 8 caractères + confirmation obligatoire

```
Emplacement : back/app/Http/Requests/User/UpdateUserRequest.php
```

---

### 2. `back/app/Http/Controllers/Api/UserController.php`

**Rôle :** C'est le "chef d'orchestre" côté backend. Il reçoit la requête HTTP (déjà validée), interagit avec le modèle `User` pour modifier la base de données, et retourne une réponse JSON.

**Méthode concernée : `update()`**

Ce qu'elle fait :
1. Récupère le user par son `id` (erreur 404 automatique si inexistant)
2. Prépare les données à mettre à jour (`name`, `email`, `role_id`)
3. Met à jour le mot de passe **seulement s'il est fourni** (pas de champ vide en base)
4. Appelle `$user->update($data)` pour enregistrer en base
5. Retourne un JSON avec le message de succès et le user mis à jour (formaté par `UserResource`)

```
Emplacement : back/app/Http/Controllers/Api/UserController.php
Méthode     : update(UpdateUserRequest $request, string $id)
Route HTTP  : PUT /api/admin/users/{id}
```

---

### 3. `back/app/Http/Resources/UserResource.php`

**Rôle :** Contrôler exactement ce que Laravel envoie à Vue.js dans le JSON. Sans cette classe, Eloquent enverrait **tous** les champs du modèle, y compris le mot de passe hashé — ce qui serait une faille de sécurité.

**Ce qu'il expose :**
- `id`, `name`, `email`, `is_active`, `created_at` (formaté en `dd/mm/YYYY`)
- `role` : objet avec `id`, `name`, `slug` — mais **seulement si la relation est chargée** (`whenLoaded`)

```
Emplacement : back/app/Http/Resources/UserResource.php
Utilisé par : show(), update(), store(), index()
```

---

### 4. `back/routes/api.php`

**Rôle :** Définir les URLs que le frontend peut appeler. `Route::apiResource('users', UserController::class)` génère automatiquement 5 routes standards :

| Méthode HTTP | URL | Action controller |
|---|---|---|
| GET | `/api/admin/users` | `index()` |
| POST | `/api/admin/users` | `store()` |
| GET | `/api/admin/users/{id}` | `show()` |
| PUT | `/api/admin/users/{id}` | `update()` |
| DELETE | `/api/admin/users/{id}` | `destroy()` |

Toutes ces routes sont protégées par `middleware('auth:sanctum')` — seul un utilisateur connecté peut les appeler.

```
Emplacement : back/routes/api.php
```

---

### 5. `front/src/api/userApi.js`

**Rôle :** Centraliser tous les appels HTTP vers le backend Laravel. C'est la couche de communication entre Vue.js et l'API. Chaque fonction correspond à une route Laravel.

**Méthode concernée : `update(id, data)`**

```js
update(id, data) {
  return apiClient.put(`/api/admin/users/${id}`, data)
}
```

Envoie une requête `PUT` avec les données du formulaire. `apiClient` est l'instance Axios configurée dans `axios.js` avec le token d'authentification Sanctum.

```
Emplacement : front/src/api/userApi.js
```

---

### 6. `front/src/stores/userStore.js`

**Rôle :** Le store Pinia est le "cerveau" de l'application Vue. Il gère l'état global (la liste des users, le user courant, les erreurs) et les actions asynchrones. Les composants Vue lisent les données depuis le store plutôt que de faire eux-mêmes des appels API.

**Fonction concernée : `updateUser(id, formData)`**

Ce qu'elle fait :
1. Active `loading` pour désactiver le bouton pendant l'appel
2. Appelle `userApi.update()` et attend la réponse
3. Met à jour `currentUser` avec les nouvelles données reçues du serveur
4. Affiche un toast de succès via PrimeVue
5. Redirige vers `/admin/utilisateurs` avec `router.push()`
6. En cas d'erreur 422 : stocke les erreurs dans `errors` pour les afficher champ par champ dans le formulaire

```
Emplacement : front/src/stores/userStore.js
Fonction    : updateUser(id, formData)
```

---

### 7. `front/src/views/admin/UserShowView.vue`

**Rôle :** La vue est ce que l'admin voit et avec lequel il interagit. Elle est "bête" dans le bon sens : elle affiche ce que le store lui donne et lui envoie les actions de l'utilisateur.

**Ce qu'elle fait pour la modification :**
- Affiche les informations actuelles du user dans une carte
- Bouton "Modifier" affiche/cache le formulaire (contrôlé par `showEditForm`)
- Le formulaire est pré-rempli grâce à un `watch(() => userStore.currentUser)` qui remplit `form` dès que les données arrivent du store
- La soumission appelle `userStore.updateUser(route.params.id, form)`
- Les erreurs de validation s'affichent sous chaque champ avec `userStore.errors.name[0]`

**Restriction admin :** un `computed isSelf` compare `userStore.currentUser.id` avec `authStore.user.id` pour empêcher l'admin de se modifier lui-même dans certains cas.

```
Emplacement : front/src/views/admin/UserShowView.vue
```

---

## Résumé des responsabilités

| Fichier | Couche | Responsabilité |
|---|---|---|
| `UpdateUserRequest.php` | Backend - Validation | Vérifie que les données sont correctes |
| `UserController.php` | Backend - Logique | Modifie la base de données |
| `UserResource.php` | Backend - Formatage | Contrôle ce qui est envoyé à Vue |
| `api.php` | Backend - Routage | Définit l'URL `PUT /api/admin/users/{id}` |
| `userApi.js` | Frontend - HTTP | Envoie la requête au bon endpoint |
| `userStore.js` | Frontend - État | Gère la réponse, les erreurs, la redirection |
| `UserShowView.vue` | Frontend - Interface | Affiche le formulaire, soumet les données |
