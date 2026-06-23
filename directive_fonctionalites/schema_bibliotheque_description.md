# Schéma de la base de données - Bibliothèque

![Schéma de la base de données](schema_base_donnees_bibliotheque.png)

## Description des tables

1. **roles** — les 4 profils : admin, gestionnaire, rh, user.
2. **permissions** — les actions précises qu'on peut autoriser (publier_document, valider_partiel, gerer_utilisateurs...).
3. **role_has_permissions** — table de liaison entre roles et permissions (un rôle a plusieurs permissions, une permission peut appartenir à plusieurs rôles).
4. **users** — les comptes utilisateurs (nom, email, mot de passe, rôle, actif/inactif).
5. **password_reset_tokens** — table technique Laravel pour la réinitialisation de mot de passe.
6. **sessions** — table technique Laravel pour gérer les sessions de connexion.
7. **personal_access_tokens** — table technique pour les tokens d'authentification API (Sanctum).
8. **categories** — les catégories/thématiques du catalogue (peut avoir des sous-catégories grâce à `parent_id`).
9. **document_references** — la table centrale du catalogue : chaque référence documentaire (titre, auteur, description, statut de validation, catégorie).
10. **documents** — les fichiers physiques (PDF...) liés à une référence ; une référence peut avoir plusieurs fichiers/versions.
11. **depot_requests** — les demandes de dépôt soumises par les utilisateurs (nouvelle référence à ajouter au catalogue).
12. **document_assignments** — trace quand l'admin assigne un document à un gestionnaire, ou le soumet à un autre responsable pour revérification.
13. **validation_steps** — l'historique de chaque décision prise pendant le circuit de validation (vérification gestionnaire, validation admin), avec commentaire/justification.
14. **download_logs** — journal de qui a téléchargé quel document et quand.
15. **consultation_logs** — journal de qui a consulté quelle référence et quand (pour les statistiques).
16. **action_histories** — journal général de toutes les actions sensibles (création de compte, désactivation, validation, publication...) pour l'historique consulté par la RH.

## Résumé

- **roles** et **permissions** gèrent les droits.
- **users** gère les comptes.
- **categories** et **document_references** forment le catalogue.
- **documents** gère les fichiers physiques.
- **depot_requests**, **document_assignments** et **validation_steps** gèrent le circuit de soumission, validation et publication.
- **download_logs**, **consultation_logs** et **action_histories** gèrent les statistiques et l'audit.
- **password_reset_tokens**, **sessions** et **personal_access_tokens** sont des tables techniques générées automatiquement par Laravel.
