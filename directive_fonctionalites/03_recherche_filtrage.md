# Recherche & Filtrage des utilisateurs

> Fonctionnalité : l'admin peut filtrer la liste des utilisateurs par nom/email (recherche textuelle), par statut (Actif/Inactif) et par rôle. Les filtres s'appliquent instantanément sans bouton "Rechercher".

---

## Circuit complet de la donnée

```
[UsersListView.vue]     ← l'admin tape dans la barre ou change un select
      ↓ watch() détecte le changement
[userStore.js]          ← filters mis à jour → fetchUsers(1) relancé
      ↓ appelle
[userApi.js]            ← GET /api/admin/users?search=jean&status=1&role_id=2
      ↓ HTTP + query string
[api.php]               ← route GET /api/admin/users → UserController@index
      ↓ dirige vers
[UserController.php]    ← index() lit les paramètres et filtre la requête SQL
      ↓ retourne JSON paginé
[userStore.js]          ← met à jour users[] et pagination
      ↓
[UsersListView.vue]     ← le tableau se re-rend automatiquement
```

---

## Concept clé : filtrage côté serveur vs côté client

Il existe deux façons de filtrer une liste :

**Côté client (mauvaise pratique ici) :**
Charger tous les utilisateurs d'un coup, puis filtrer en JavaScript dans le navigateur.
- ❌ Problème : si on a 10 000 utilisateurs, on les charge tous inutilement
- ❌ Problème : incompatible avec la pagination

**Côté serveur (ce qu'on fait) :**
Envoyer les filtres à Laravel qui construit la requête SQL adaptée et ne retourne que les résultats correspondants.
- ✅ Performant : la base de données fait le travail
- ✅ Compatible avec la pagination
- ✅ `LIKE '%jean%'` dans MySQL est optimisé pour ça

---

## Fichiers impliqués

### 1. `back/app/Http/Controllers/Api/UserController.php`

**Rôle :** La méthode `index()` construit dynamiquement la requête SQL selon les filtres reçus dans l'URL.

**Méthode concernée : `index(Request $request)`**

**Comment ça marche :**

On commence par une requête "de base" sans filtre, puis on ajoute des conditions `WHERE` selon ce que l'admin a sélectionné :

```php
$query = User::with('role')->orderBy('created_at', 'desc');

// Filtre texte (nom OU email)
if ($request->filled('search')) {
    $search = $request->search;
    $query->where(function ($q) use ($search) {
        $q->where('name', 'LIKE', "%{$search}%")
          ->orWhere('email', 'LIKE', "%{$search}%");
    });
}

// Filtre statut
if ($request->has('status') && $request->status !== '') {
    $query->where('is_active', (bool) $request->status);
}

// Filtre rôle
if ($request->filled('role_id')) {
    $query->where('role_id', $request->role_id);
}
```

**Pourquoi `$request->filled()` et pas `$request->has()` ?**
- `has('search')` = vrai même si la valeur est vide (`?search=`)
- `filled('search')` = vrai **seulement** si la valeur est non vide → évite des filtres inutiles

**Cas spécial du statut :**
On utilise `has()` + vérification manuelle `!== ''` plutôt que `filled()` parce que `"0"` (inactif) est falsy en PHP — `filled('0')` retournerait `false` alors que c'est un filtre valide.

**`appends($request->query())` :**
Cette méthode ajoute automatiquement les paramètres de filtre aux liens de pagination générés par Laravel. Sans ça, le lien "page 2" perdrait les filtres actifs.

```
Emplacement : back/app/Http/Controllers/Api/UserController.php
Méthode     : index(Request $request)
Route HTTP  : GET /api/admin/users?search=...&status=...&role_id=...&page=...
```

---

### 2. `back/routes/api.php`

**Rôle :** Aucune modification nécessaire ici — `Route::apiResource('users', UserController::class)` gère déjà `GET /api/admin/users` et dirige vers `index()`. Les query strings (`?search=...`) ne font pas partie de la définition de route, Laravel les lit via `$request`.

```
Emplacement : back/routes/api.php
Route concernée : GET /api/admin/users (déjà existante)
```

---

### 3. `front/src/api/userApi.js`

**Rôle :** Envoyer les filtres à Laravel dans l'URL sous forme de query string.

**Méthode concernée : `getAll(page, filters)`**

```js
getAll(page = 1, filters = {}) {
  return apiClient.get('/api/admin/users', {
    params: {
      page,
      ...filters,   // { search: 'jean', status: '1', role_id: '2' }
    }
  })
}
```

**Pourquoi `params` et pas une URL construite manuellement ?**

Axios sérialise automatiquement l'objet `params` en query string propre :
```
{ page: 1, search: 'jean', status: '1' }
→ /api/admin/users?page=1&search=jean&status=1
```

Si on construisait l'URL manuellement avec des template literals, on risquerait des problèmes d'encodage (espaces, caractères spéciaux). `params` gère ça automatiquement.

**L'opérateur spread `...filters` :**
Il "étale" toutes les propriétés de l'objet `filters` dans `params`. Si `filters = { search: 'jean', status: '1' }`, le résultat sera `{ page: 1, search: 'jean', status: '1' }`.

```
Emplacement : front/src/api/userApi.js
Méthode     : getAll(page, filters)
```

---

### 4. `front/src/stores/userStore.js`

**Rôle :** Stocker l'état des filtres et les transmettre à chaque appel API.

**Nouveautés ajoutées :**

**L'état `filters` :**
```js
const filters = ref({
  search  : '',   // '' = pas de filtre texte
  status  : '',   // '' = tous | '1' = actifs | '0' = inactifs
  role_id : '',   // '' = tous les rôles
})
```

Pourquoi stocker les filtres dans le **store** et pas directement dans la vue ?
- Le store est partagé entre les composants → si on navigue et revient, les filtres sont préservés
- La vue reste simple : elle lit les filtres du store, elle ne les gère pas elle-même

**La fonction `fetchUsers` mise à jour :**
```js
async function fetchUsers(page = 1) {
  // Nettoyage : on retire les filtres avec valeur '' pour ne pas
  // envoyer ?search=&status=&role_id= à Laravel
  const activeFilters = Object.fromEntries(
    Object.entries(filters.value).filter(([, v]) => v !== '')
  )
  const response = await userApi.getAll(page, activeFilters)
  // ...
}
```

`Object.fromEntries(Object.entries(...).filter(...))` : on convertit l'objet en tableau de paires `[clé, valeur]`, on filtre les valeurs vides, puis on reconvertit en objet.

**La fonction `resetFilters()` :**
```js
function resetFilters() {
  filters.value = { search: '', status: '', role_id: '' }
  fetchUsers(1)
}
```

Remet tout à zéro et recharge la première page — appelée par le bouton "✕ Réinitialiser".

```
Emplacement : front/src/stores/userStore.js
Nouveautés  : state filters, resetFilters(), fetchUsers() améliorée
```

---

### 5. `front/src/views/admin/UsersListView.vue`

**Rôle :** Afficher les contrôles de filtrage et déclencher automatiquement la recherche à chaque changement.

**Les contrôles de filtre :**

```vue
<!-- v-model lie directement sur userStore.filters.search -->
<input v-model="userStore.filters.search" placeholder="Nom ou adresse e-mail..." />

<select v-model="userStore.filters.status">
  <option value="">Tous les statuts</option>
  <option value="1">Actifs</option>
  <option value="0">Inactifs</option>
</select>

<select v-model="userStore.filters.role_id">
  <option value="">Tous les rôles</option>
  <option v-for="role in userStore.roles" :key="role.id" :value="role.id">
    {{ role.name }}
  </option>
</select>
```

**Le `watch` — déclencheur automatique :**

```js
watch(
  () => ({ ...userStore.filters }),  // on surveille une copie des filtres
  () => {
    userStore.fetchUsers(1)          // dès qu'un filtre change → page 1
  },
  { deep: true }
)
```

`deep: true` est nécessaire parce qu'on surveille un objet — sans ça, Vue ne détecterait pas les changements sur les propriétés internes (`search`, `status`, `role_id`).

**Pourquoi `() => ({ ...userStore.filters })` et pas juste `userStore.filters` ?**
Le spread `...` crée une copie à chaque évaluation. Sans ça, Vue comparerait la même référence d'objet avec elle-même et ne détecterait aucun changement.

**Le bouton réinitialiser conditionnel :**
```vue
<!-- Affiché seulement si au moins un filtre est actif -->
<button
  v-if="userStore.filters.search || userStore.filters.status !== '' || userStore.filters.role_id !== ''"
  @click="userStore.resetFilters()"
>
  ✕ Réinitialiser
</button>
```

**Le compteur de résultats :**
```vue
<p>{{ userStore.pagination.total }} utilisateur(s) trouvé(s)</p>
```

`pagination.total` est automatiquement renvoyé par Laravel dans le JSON de pagination — c'est le nombre total de résultats correspondant aux filtres actifs, pas seulement ceux de la page courante.

```
Emplacement : front/src/views/admin/UsersListView.vue
Nouveautés  : barre de recherche, 2 selects, watch(), bouton reset, compteur
```

---

## Résumé des responsabilités

| Fichier | Couche | Responsabilité |
|---|---|---|
| `UserController.php` | Backend - Logique | Construit la requête SQL selon les filtres reçus |
| `api.php` | Backend - Routage | Route déjà existante, aucune modification |
| `userApi.js` | Frontend - HTTP | Sérialise les filtres en query string via `params` |
| `userStore.js` | Frontend - État | Stocke `filters`, nettoie les valeurs vides, expose `resetFilters()` |
| `UsersListView.vue` | Frontend - Interface | Contrôles de filtre, `watch()` auto-trigger, compteur de résultats |

---

## Schéma de la requête SQL générée

Selon les filtres actifs, Laravel construit différentes requêtes :

**Aucun filtre :**
```sql
SELECT * FROM users
ORDER BY created_at DESC
LIMIT 15 OFFSET 0
```

**search = "jean" + status = "1" :**
```sql
SELECT * FROM users
WHERE (name LIKE '%jean%' OR email LIKE '%jean%')
AND is_active = 1
ORDER BY created_at DESC
LIMIT 15 OFFSET 0
```

**role_id = "3" :**
```sql
SELECT * FROM users
WHERE role_id = 3
ORDER BY created_at DESC
LIMIT 15 OFFSET 0
```

C'est Eloquent (l'ORM de Laravel) qui construit ces requêtes à partir des appels `->where()` chaînés dans `index()`.
