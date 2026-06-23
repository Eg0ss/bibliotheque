# Activation & Désactivation d'un utilisateur

> Fonctionnalité : l'admin peut basculer le statut d'un compte entre "Actif" et "Inactif" en un clic. Un admin ne peut pas désactiver son propre compte.

---

## Circuit complet de la donnée

```
[UserShowView.vue]      ← clic bouton "Activer" ou "Désactiver"
      ↓ appelle
[userStore.js]          ← toggleUserStatus(id)
      ↓ appelle
[userApi.js]            ← PATCH /api/admin/users/{id}/toggle-status
      ↓ HTTP
[api.php]               ← route PATCH enregistrée manuellement
      ↓ dirige vers
[UserController.php]    ← méthode toggleStatus() inverse is_active en base
      ↓ retourne JSON
[userStore.js]          ← met à jour is_active dans currentUser + toast
      ↓
[UserShowView.vue]      ← le badge de statut se met à jour automatiquement
```

---

## Concept clé : le "toggle"

Le mot **toggle** signifie "basculer". Au lieu d'avoir deux routes séparées (`/activate` et `/deactivate`), on en a une seule qui **inverse** la valeur actuelle :

```php
// Si is_active = true  → devient false (désactivé)
// Si is_active = false → devient true  (activé)
$user->update(['is_active' => !$user->is_active]);
```

C'est plus simple, plus propre, et le frontend n'a pas besoin de savoir l'état actuel pour choisir quelle route appeler.

---

## Fichiers impliqués

### 1. `back/app/Http/Controllers/Api/UserController.php`

**Rôle :** Contient la méthode `toggleStatus()` qui gère la bascule en base de données.

**Méthode concernée : `toggleStatus()`**

Ce qu'elle fait dans l'ordre :
1. Récupère le user par `$id` (404 automatique si inexistant)
2. **Vérifie si l'admin essaie de se désactiver lui-même** → retourne une erreur 403 (Forbidden) avec un message explicite
3. Inverse `is_active` avec l'opérateur `!` (not)
4. Retourne le nouveau statut et un message de confirmation

**Pourquoi la vérification `auth()->id()` ?**
`auth()->id()` retourne l'id de l'utilisateur **actuellement connecté** (lu depuis le token Sanctum). Si cet id est identique à l'id du user qu'on veut modifier, c'est qu'il essaie de se désactiver lui-même — ce qu'on interdit.

```
Emplacement : back/app/Http/Controllers/Api/UserController.php
Méthode     : toggleStatus(string $id)
Route HTTP  : PATCH /api/admin/users/{id}/toggle-status
Code retour : 200 (succès) ou 403 (auto-désactivation interdite)
```

---

### 2. `back/routes/api.php`

**Rôle :** La route `toggle-status` n'est **pas** générée par `apiResource` car c'est une action personnalisée. Elle est déclarée manuellement :

```php
Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
```

**Pourquoi `PATCH` et pas `PUT` ?**
- `PUT` = remplacer **toute** la ressource (tous les champs)
- `PATCH` = modifier **partiellement** la ressource (juste `is_active` ici)

C'est une convention REST : on utilise `PATCH` pour les modifications partielles.

```
Emplacement : back/routes/api.php
URL         : PATCH /api/admin/users/{id}/toggle-status
```

---

### 3. `front/src/api/userApi.js`

**Rôle :** Envoyer la requête `PATCH` au bon endpoint. Pas de body (corps) à envoyer — l'id dans l'URL suffit au backend pour trouver et inverser le statut.

**Méthode concernée : `toggleStatus(id)`**

```js
toggleStatus(id) {
  return apiClient.patch(`/api/admin/users/${id}/toggle-status`)
}
```

```
Emplacement : front/src/api/userApi.js
Méthode     : toggleStatus(id)
```

---

### 4. `front/src/stores/userStore.js`

**Rôle :** Gérer la réponse de l'API et mettre à jour l'état **sans recharger toute la page**.

**Fonction concernée : `toggleUserStatus(id)`**

Ce qu'elle fait :
1. Appelle `userApi.toggleStatus(id)`
2. Récupère `is_active` depuis la réponse du serveur (c'est la valeur après bascule)
3. Met à jour `currentUser.is_active` → le badge dans la vue se met à jour **instantanément** grâce à la réactivité Vue
4. Met à jour aussi l'entrée correspondante dans `users` (la liste) si elle est chargée
5. Affiche un toast avec le message retourné par Laravel (`"Compte activé avec succès."` ou `"Compte désactivé avec succès."`)
6. En cas d'erreur 403 : affiche le message d'interdiction

**Pourquoi mettre à jour les deux (`currentUser` ET `users`) ?**
Si l'admin revient à la liste sans recharger la page, le statut doit être déjà à jour dans `users`. On évite ainsi un appel API supplémentaire.

```
Emplacement : front/src/stores/userStore.js
Fonction    : toggleUserStatus(id)
```

---

### 5. `front/src/views/admin/UserShowView.vue`

**Rôle :** Afficher le bouton et la restriction visuelle.

**Ce qu'elle fait pour le toggle :**

**Le bouton change de texte et de couleur automatiquement** selon `currentUser.is_active` :
```vue
:class="userStore.currentUser.is_active
  ? 'bg-orange-100 text-orange-700'   ← si actif → bouton orange "Désactiver"
  : 'bg-green-100 text-green-700'"    ← si inactif → bouton vert "Activer"
```

**La restriction visuelle `isSelf` :**

```js
// computed : recalculé automatiquement quand currentUser ou authStore.user change
const isSelf = computed(() =>
  userStore.currentUser?.id === authStore.user?.id
)
```

Si `isSelf` est `true` (l'admin regarde sa propre fiche) :
- Le bouton est **masqué** avec `v-if="!isSelf"`
- Un texte explicatif apparaît à la place : `"Vous ne pouvez pas modifier votre propre statut"`

Cette restriction est **double** : côté frontend (bouton masqué) ET côté backend (erreur 403) — la sécurité ne repose jamais sur une seule couche.

```
Emplacement : front/src/views/admin/UserShowView.vue
```

---

## Le badge de statut dans la liste

Dans `UsersListView.vue`, le badge se colore automatiquement selon `user.is_active` :

```vue
<span :class="user.is_active
  ? 'bg-green-100 text-green-700'
  : 'bg-red-100 text-red-700'"
>
  {{ user.is_active ? 'Actif' : 'Inactif' }}
</span>
```

Pas de logique supplémentaire nécessaire — la réactivité Vue fait le travail.

---

## Résumé des responsabilités

| Fichier | Couche | Responsabilité |
|---|---|---|
| `UserController.php` | Backend - Logique | Inverse `is_active`, bloque l'auto-désactivation (403) |
| `api.php` | Backend - Routage | Déclare `PATCH /api/admin/users/{id}/toggle-status` |
| `userApi.js` | Frontend - HTTP | Envoie la requête PATCH |
| `userStore.js` | Frontend - État | Met à jour `currentUser` et `users` sans rechargement |
| `UserShowView.vue` | Frontend - Interface | Bouton dynamique + restriction visuelle `isSelf` |

---

## Pourquoi cette double sécurité admin/soi-même ?

| Couche | Mécanisme | Ce qu'il bloque |
|---|---|---|
| Frontend | `v-if="!isSelf"` masque le bouton | L'admin ne voit pas le bouton sur sa fiche |
| Backend | `auth()->id() === $id` → 403 | Même si quelqu'un contourne le frontend (Postman, curl...) |

Un principe fondamental en sécurité web : **ne jamais faire confiance uniquement au frontend**.
