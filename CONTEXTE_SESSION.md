# Contexte de session — TilkiPlateforme

> Ouvrir ce fichier au début d'une nouvelle session Claude Code pour reprendre le travail.
> Dernière mise à jour : 2026-06-21

---

## Projet

**TilkiPlateforme** — portail client PHP 8.3 pour TILKI, courtier en assurances.
Répertoire : `/root/TilkiPlateforme`

---

## Stack technique

| Élément | Valeur |
|---|---|
| Langage | PHP 8.3 |
| Base de données | MySQL 8 — utf8mb4/InnoDB |
| Frontend | Bootstrap 5.3 + Bootstrap Icons 1.11 |
| Autoloader | PSR-4 : `App\` → `app/` |
| Router | `public/index.php` (custom) |
| Architecture | MVC maison |
| Serveur | Nginx + PHP-FPM, HTTPS auto-signé, IP `72.61.180.223` |

### Conventions importantes
- **CSRF** : `$_SESSION['csrf_token']`, `hash_equals`, champ `_csrf`
- **Flash client** : `$_SESSION['flash']` — **Flash admin** : `$_SESSION['admin_flash']`
- **Fichiers** : `FileStorage::store()` / `FileStorage::serve()` / `FileStorage::serveInline()` (via DocumentController::view)
- **Audit** : `AuditLogger::log(actor_type, actor_id, action, target, ip)`
- **Encodage URL** : `http_build_query(..., PHP_QUERY_RFC3986)`
- **Config** : secrets via `env()` (chargeur `config/env.php` + `.env` à la racine)

---

## Accès / identifiants de test

| Compte | Email | Mot de passe |
|---|---|---|
| Admin | admin@tilki.sn | Admin@Tilki2024 |
| Client test | compte 100001 | PIN : 12345678 (initial) |

### Config BDD (via `.env` — ne pas commiter)
```
DB_HOST=127.0.0.1  DB_PORT=3306  DB_NAME=tilki_portal
DB_USER=tilki_user  DB_PASS=<dans .env local>
```

### Config Tally (via `.env`)
- `TALLY_SECRET` et `TALLY_CLAIM_FORM_URL` dans `.env`
- URL formulaire sinistre : `https://tally.so/r/b5AD6E`

### Push GitHub
```bash
git remote set-url origin https://<TOKEN>@github.com/elantra93/TilkiPlateforme.git && git push origin main
```
> Régénérer le token sur https://github.com/settings/tokens si expiré.

---

## Historique des commits récents

```
f476e60 feat(deploy): config sans secret, env.php, .htaccess, README_DEPLOY
c19a7d7 feat(docs): Blocs 5+6 — upload contextuel, suppression page standalone
d19074d feat(payments): Bloc 4 — gestion des paiements (migration, modèle, vues admin+client)
6a5f7ea fix(claims): bouton Accéder au formulaire grisé — modale déclaration sinistre
194979c fix: boutons steps sinistre, logo parapluie unique, favicons, schema.sql
```

---

## Fonctionnalités implémentées (terminées)

### Session 2026-06-19

#### Déploiement Git autonome
- `config/config.php` versionné, 100 % `env()`, URL figée `https://tilki.digital`
- `config/env.php` : chargeur `.env` (aucun secret)
- `.env.example` : modèle versionné avec toutes les clés
- `.htaccess` racine : `Deny from all`
- `README_DEPLOY.md` : procédure complète de déploiement

#### Blocs 5+6 — Upload documents contextuel
- `/admin/documents/upload` supprimé → redirige vers `/admin/documents/pending`
- Nav admin : lien "Uploader" retiré
- **Fiche client** : section "Documents dossier client" (scope=`client`, 6 types)
- **Fiche contrat** : section "Documents du contrat" (scope=`contrat`, cascade catégorie→type branch-aware)
- **Fiche sinistre** : `claimDocTypeSel` plus `disabled` à l'init ; labels FR lisibles ; chemin d'erreur corrigé
- `Document::forClientScope()` et `Document::forContractAdmin()` ajoutés

#### Bloc 4 — Paiements
- Table `payments`, modèle `Payment`, `AdminPaymentController`, `PaymentController`
- Vue admin (liste + formulaire) et vue client (lecture seule + téléchargement preuve)
- Bouton "Enregistrer un paiement" sur fiche client et fiche contrat

#### Bouton « Accéder au formulaire » (modale sinistre)
- `shown.bs.modal` + IIFE — couvre le cas un seul contrat pré-sélectionné
- `target="_blank"` : Tally s'ouvre dans un nouvel onglet

#### Boutons étapes sinistre + logos + favicons
- `<form>` dans `<td>`, hidden inputs sync, `isset` → `!empty`
- Logo parapluie unifié (filtre CSS blanc sur fonds sombres)
- Favicons sur forgot_password et reset_password

---

## Migrations BDD — état

| Migration | BDD prod |
|---|---|
| migration_001 (catégories sinistre) | ✅ appliquée |
| migration_002 (claim_steps, is_auto_rc) | ✅ appliquée |
| migration_003 (scope+category `carte`) | ✅ appliquée |
| migration_004 (payments + scope `paiement`) | ✅ appliquée |
| migration_005 (scope+category `client`) | ✅ appliquée |

**Toutes les migrations sont à jour en production.**

---

## Architecture des fichiers importants

```
TilkiPlateforme/
├── app/
│   ├── Controllers/
│   │   ├── AccountController.php
│   │   ├── AdminClaimController.php       ← steps (hidden inputs), uploadDoc
│   │   ├── AdminClientController.php      ← uploadCarte, uploadDoc (scope=client)
│   │   ├── AdminContractController.php    ← uploadDoc (scope=contrat)
│   │   ├── AdminDocumentController.php    ← pending + validate ; upload → redirect
│   │   ├── AdminPaymentController.php     ← index, showCreate, create
│   │   ├── PaymentController.php          ← index client (lecture seule)
│   │   ├── ClaimController.php
│   │   ├── ContractController.php
│   │   ├── DocumentController.php
│   │   └── TallyClaimWebhookController.php
│   ├── Models/
│   │   ├── Client.php
│   │   ├── Claim.php / ClaimStep.php
│   │   ├── Contract.php
│   │   ├── Document.php                   ← forClientScope, forContractAdmin
│   │   └── Payment.php                    ← listByClient, listByContract, listAll
│   ├── Services/
│   │   ├── Auth.php / AuditLogger.php
│   │   ├── ContractDocTypes.php
│   │   ├── Database.php / FileStorage.php
│   │   └── TallyUrlBuilder.php
│   └── Views/
│       ├── admin/claims/form.php          ← upload sinistre (doc type non disabled)
│       ├── admin/clients/edit.php         ← carte + docs client
│       ├── admin/contracts/form.php       ← docs contrat + upload
│       ├── admin/payments/create.php + index.php
│       ├── payments/index.php             ← vue client paiements
│       └── …
├── config/
│   ├── config.php                         ← versionné, 100% env()
│   └── env.php                            ← chargeur .env (versionné)
├── database/
│   ├── schema.sql                         ← ENUMs complets + payments
│   ├── migration_001 → 005
├── .env.example                           ← modèle versionné (sans valeurs)
├── .htaccess                              ← Deny from all (racine)
├── public/
│   ├── index.php                          ← router
│   ├── .htaccess                          ← front-controller + security headers
│   └── logoparapluie.svg
└── README_DEPLOY.md
```

---

## Session 2026-06-21 (suite)

### BLOC 1 – Tableaux responsive
- `tbl-card-mobile` + `data-label` ajoutés sur : `admin/payments/index.php`, `admin/documents/pending.php`, `payments/index.php` (client)

### BLOC 2 – Lignes cliquables
- `app.js` : stopPropagation sur liens/boutons internes, navigation clavier (Entrée), tabindex=0
- `data-href` + classe `tbl-row-link` sur toutes les listes : contrats, sinistres, clients (client + admin)

### BLOC 3 – Popup de vérification (CSP stricte, JS externe)
- `verify-modal.js` : modale Bootstrap `#verifyModal` dans admin footer, données via `data-*`
- `AdminDocumentController::preview()` et `adminDownload()` : endpoints sécurisés admin
- Routes GET : `/admin/documents/{id}/preview`, `/admin/documents/{id}/download`
- `pending.php` : bouton « Vérifier » remplace formulaire inline + confirm() supprimé
- Fiche contrat admin : bouton « Vérifier » sur paiements en attente

### Tâches 1-3 implémentées
- **Refus docs** : `Document::rejectDoc()`, `AdminDocumentController::reject()`, route POST `/admin/documents/{id}/reject`
- **Email** : `Mailer::send()` appelé sur validate et reject (mail() + SMTP selon config)
- **Pagination sinistres** : `Claim::countAll/allPaginated`, 20/page, contrôles Bootstrap

### Audit prod 2026-06-21 — bugs corrigés
- **Fatal error** : `use App\Models\Client` dupliqué dans `AdminDocumentController` → 500 sur `/admin/documents/pending`
- **Downloads admin** : liens `/documents/{id}/download` (route cliente) dans 4 vues admin → AuthMiddleware rejetait l'admin faute de `client_id` ; corrigé en `/admin/documents/{id}/download`
- **Note** : `.htaccess` ignoré par Nginx (X-Frame-Options, CSP, php_flag tous inactifs) ; seul HSTS est dans nginx.conf
- **Note** : Token GitHub expiré — régénérer sur https://github.com/settings/tokens

## Tâches restantes

*(Toutes les tâches 1-3 initiales sont terminées. BLOCs 1/2/3 livrés.)*
