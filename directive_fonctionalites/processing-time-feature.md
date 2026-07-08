# Fonctionnalité : Mesure du temps réel de traitement d'une référence

## Objectif

Cette fonctionnalité permet de mesurer le **temps réel passé par un gestionnaire sur une référence**.

Contrairement à une simple date d'assignation, le chronomètre ne démarre que lorsque le gestionnaire ouvre réellement le document, puis s'arrête lorsqu'il prend sa décision (Accepter ou Rejeter).

La durée est ensuite calculée automatiquement et affichée dans l'interface administrateur.

---

# Règles métier

Le fonctionnement repose sur les règles suivantes :

- Le chronomètre démarre lors de la première ouverture du document.
- Le chronomètre ne démarre qu'une seule fois.
- Le chronomètre s'arrête lorsque le gestionnaire valide une décision.
- Les dates sont enregistrées telles quelles.
- La durée n'est jamais enregistrée en base de données.
- La durée est calculée dynamiquement grâce à :

```
processed_at - processing_started_at
```

---

# Cheminement complet de la fonctionnalité

La fonctionnalité traverse plusieurs couches de l'application.

```
Administrateur
        │
        ▼
AssignmentView.vue
        │
        ▼
assignmentStore.js
        │
        ▼
API Laravel
        │
        ▼
AdminDepotRequestController
        │
        ▼
DocumentAssignment Model
        │
        ▼
Base de données
```

En parallèle, côté gestionnaire :

```
Gestionnaire ouvre un document
        │
        ▼
GestionnaireController::show()
        │
        ▼
processing_started_at = now()
```

Puis :

```
Gestionnaire clique Accepter/Rejeter
        │
        ▼
GestionnaireController::decide()
        │
        ▼
processed_at = now()
```

---

# Étape 1 — Base de données

## Fichier

```
back/database/migrations/2026_07_07_100000_add_processing_times_to_document_assignments_table.php
```

## Rôle

Ajoute les deux colonnes nécessaires.

```php
processing_started_at
processed_at
```

Ces colonnes enregistrent :

- début réel du traitement
- fin réelle du traitement

Aucune durée n'est stockée.

---

# Étape 2 — Modèle DocumentAssignment

## Fichier

```
back/app/Models/DocumentAssignment.php
```

Le modèle centralise toute la logique métier liée à une assignation.

---

## 2.1 Fillable

Ajout :

```php
processing_started_at
processed_at
```

Permet leur mise à jour via :

```php
$assignment->update(...)
```

---

## 2.2 Casts

Ajout :

```php
'processing_started_at' => 'datetime',
'processed_at' => 'datetime',
```

Laravel transforme automatiquement les timestamps en objets Carbon.

---

## 2.3 Méthode

```
getProcessingDuration()
```

### Rôle

Calculer dynamiquement la durée réelle.

La méthode :

- vérifie que les deux dates existent
- calcule la différence
- retourne un objet prêt à être utilisé par l'API.

Exemple :

```php
[
    "total_seconds" => 7800,
    "label" => "2h 10min"
]
```

Cette méthode évite de stocker une durée en base.

---

# Étape 3 — Première ouverture du document

## Fichier

```
back/app/Http/Controllers/Api/GestionnaireController.php
```

## Méthode

```
show(string $id)
```

## Rôle

Retourner le document au gestionnaire.

C'est également ici que démarre le chronomètre.

Logique :

```php
if (is_null($assignment->processing_started_at)) {
    $assignment->update([
        'processing_started_at' => now()
    ]);
}
```

Pourquoi ?

Le gestionnaire peut ouvrir le document plusieurs fois.

Le premier accès :

```
processing_started_at = maintenant
```

Les suivants :

```
aucune modification
```

Le temps réel reste donc exact.

---

# Étape 4 — Décision du gestionnaire

## Fichier

```
back/app/Http/Controllers/Api/GestionnaireController.php
```

## Méthode

```
decide(Request $request, string $id)
```

## Rôle

Enregistrer la décision :

- Accepté
- Rejeté

Puis arrêter le chronomètre.

```php
$assignment->update([
    'processed_at' => now()
]);
```

Cette date représente la fin réelle du traitement.

---

# Étape 5 — Exposition des données à l'API

## Fichier

```
back/app/Http/Controllers/Api/AdminDepotRequestController.php
```

## Méthode

```
assignments()
```

## Rôle

Construire la réponse JSON envoyée au frontend.

Les nouveaux champs ajoutés sont :

```php
processing_started_at

processed_at

processing_duration
```

Exemple :

```json
{
    "processing_started_at": "...",
    "processed_at": "...",
    "processing_duration": {
        "label": "2h 15min"
    }
}
```

Le frontend reçoit alors toutes les informations nécessaires.

---

# Étape 6 — Store Vue

## Fichier

```
front/src/stores/assignmentStore.js
```

## Méthode

```
fetchAssignments()
```

## Rôle

Appeler :

```
GET /api/admin/assignments
```

Les nouvelles propriétés sont automatiquement stockées dans :

```js
store.assignments
```

Chaque assignation possède désormais :

```js
assignment.processing_started_at

assignment.processed_at

assignment.processing_duration
```

Aucune logique supplémentaire n'est nécessaire.

---

# Étape 7 — Interface administrateur

## Fichier

```
front/src/views/admin/AssignmentView.vue
```

Cette vue affiche le tableau des assignations.

---

## 7.1 Fonction

```
formatDateTime()
```

### Rôle

Afficher proprement :

```
07/07/2026 14:35
```

et retourner

```
—
```

si la valeur est nulle.

---

## 7.2 En-tête du tableau

Ajout de trois colonnes :

```
Début traitement

Fin traitement

Durée totale
```

---

## 7.3 Corps du tableau

Ajout des champs :

```vue
formatDateTime(a.processing_started_at)

formatDateTime(a.processed_at)

a.processing_duration?.label ?? "—"
```

Le résultat est :

| Début | Fin | Durée |
|--------|-----|--------|
|07/07/2026 14:03|07/07/2026 15:16|1h 13min|

---

# Déroulement complet de la fonctionnalité

## 1.

L'administrateur assigne une référence.

↓

Une ligne est créée dans

```
document_assignments
```

↓

```
processing_started_at = NULL

processed_at = NULL
```

---

## 2.

Le gestionnaire ouvre la référence.

↓

```
GestionnaireController::show()
```

↓

```
processing_started_at = now()
```

---

## 3.

Le gestionnaire continue son travail.

↓

Il peut fermer puis rouvrir le document.

↓

La date de début ne change jamais.

---

## 4.

Le gestionnaire clique :

```
Accepter
```

ou

```
Rejeter
```

↓

```
GestionnaireController::decide()
```

↓

```
processed_at = now()
```

---

## 5.

L'administrateur ouvre la page :

```
AssignmentView.vue
```

↓

Le Store appelle :

```
GET /api/admin/assignments
```

↓

Le contrôleur construit la réponse :

```
processing_started_at

processed_at

processing_duration
```

↓

Le frontend affiche :

```
Début traitement

Fin traitement

Durée totale
```

---

# Résultat obtenu

Le système permet désormais :

- connaître l'heure réelle de début du traitement ;
- connaître l'heure réelle de fin ;
- mesurer le temps effectivement passé par le gestionnaire ;
- produire des statistiques fiables sur les performances des gestionnaires ;
- conserver les données brutes pour de futures analyses (temps moyen, temps par type de document, temps par gestionnaire, etc.).

La fonctionnalité respecte les règles métier en ne démarrant le chronomètre qu'une seule fois, en arrêtant celui-ci uniquement lors de la décision finale, et en calculant la durée à partir des dates enregistrées plutôt qu'en stockant une valeur dérivée.