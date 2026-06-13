# TILKI – Portail Client

## Contexte
TILKI est une société de courtage en assurance. Ce portail permet à chaque client de consulter et télécharger la documentation de ses contrats et sinistres, et de déposer des preuves de règlement.

## Stack technique
- **Backend** : PHP 8.3
- **Base de données** : MySQL 8 (utf8mb4, InnoDB)
- **Frontend** : Bootstrap 5 (CDN)
- **Hébergement** : VPS Ubuntu 24.04

## Authentification
- Connexion par numéro de compte à 6 chiffres + mot de passe
- Hachage bcrypt (password_hash / password_verify)
- Limitation des tentatives (5 échecs → blocage 15 min)
- Changement de mot de passe forcé à la première connexion
- Sessions sécurisées (httponly, secure, samesite=Strict)

## Structure des dossiers
```
public/          ← seul dossier exposé (index.php + assets)
app/
  Controllers/   ← AuthController, DashboardController, ContractController, ClaimController, DocumentController
  Models/        ← Client, Contract, Claim, Document, LoginAttempt
  Views/         ← layout, auth, dashboard, contracts, claims, errors
  Services/      ← Database (PDO singleton), Auth, FileStorage, AuditLogger
  Middleware/    ← AuthMiddleware
config/          ← config.php (non versionné) + config.sample.php
storage/         ← documents + logs (hors web)
database/        ← schema.sql
scripts/         ← seed.php
```

## Tables MySQL
- `clients` – comptes clients avec numéro à 6 chiffres
- `contracts` – contrats d'assurance
- `claims` – sinistres
- `documents` – fichiers liés aux contrats et sinistres
- `admins` – comptes administrateurs
- `login_attempts` – historique des tentatives de connexion
- `audit_log` – journal d'audit complet

## Règles de sécurité
- HTTPS forcé (301 redirect)
- Fichiers servis uniquement via `/documents/{id}/download` après vérification de session ET de propriété
- Tokens CSRF sur tous les formulaires POST
- Aucune donnée sensible en clair (mots de passe, chemins de fichiers)
- En-têtes de sécurité HTTP (X-Frame-Options, CSP, etc.)
- Logs d'audit pour téléchargements et dépôts
