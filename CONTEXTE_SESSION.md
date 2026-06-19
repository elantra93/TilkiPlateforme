# Contexte de session — TilkiPlateforme

> Ouvrir ce fichier au début d'une nouvelle session Claude Code pour reprendre le travail.
> Dernière mise à jour : 2026-06-19

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

---

## Accès / identifiants de test

| Compte | Email | Mot de passe |
|---|---|---|
| Admin | admin@tilki.sn | Admin@Tilki2024 |
| Client test | compte 100001 | PIN : 12345678 (initial) |

### Config BDD (`config/config.php`)
```
host=127.0.0.1  port=3306  db=tilki_portal
user=tilki_user  pass=TilkiDB_2024!
```

### Config Tally
- Secret HMAC : `19c9dcdc8e49f97c28541ca3b4a172c75d678dab57b69e6e8fccaffa7d67aa46`
- URL formulaire sinistre : `https://tally.so/r/b5AD6E`

---

## Historique des commits récents

```
(en attente de push) feat: Bloc 4 — paiements (migration_004, Payment model, vues admin+client)
6a5f7ea fix(claims): bouton Accéder au formulaire grisé — modale déclaration sinistre
194979c fix: boutons steps sinistre, logo parapluie unique, favicons, schema.sql
0b66da2 feat: BLOCs 2-7 — PIN client, documents, paramètres, carte assurance, logos
f3491d8 fix(hostinger): ROOT_PATH détection répertoire sibling tilki_app
35447ee feat(admin/claims): parcours Tally pour la création de sinistre
9c4bbb5 feat(ui): 3 états visuels pour les sections documentaires (rouge/gris/vert)
```

> **Push** : `git remote set-url origin https://<TOKEN>@github.com/elantra93/TilkiPlateforme.git && git push origin main`
> Le token précédent a été exposé — en régénérer un sur GitHub.

---

## Fonctionnalités implémentées (terminées)

### Session 2026-06-19 — Corrections

#### Boutons étapes sinistre (admin/claims/form.php)
- `<form>` était imbriqué dans `<tr>` (HTML invalide → foster-parenté hors tableau par le browser)
- Corrigé : form déplacé dans le dernier `<td>`, avec hidden inputs `completed`/`completed_date`
- JS mis à jour pour syncer checkbox + date → hidden inputs avant submit
- `AdminClaimController::updateStep` : `isset($_POST['completed'])` → `!empty()`

#### Logo unique parapluie
- Tous les headers (client bg-primary, admin bg-dark) : `logoparapluie.svg` + `filter: brightness(0) invert(1)` (blanc)
- Pages login (fond clair / carte blanche) : `logoparapluie.svg` bleu direct, h=64/56
- `logoblanc.svg` et `logobleu.svg` ne sont plus référencés dans les vues

#### Favicons manquants
- Ajout `<link rel="icon" href="/logoparapluie.svg">` dans `auth/forgot_password.php` et `auth/reset_password.php`

#### Label technique
- `admin/contracts/form.php` : "Restant dû (premium_due)" → "Restant dû"

#### Carte d'assurance non affichée côté client (Bloc 2)
- **Racine** : `migration_003` non exécutée → `scope='carte'` absent de l'ENUM → INSERT silencieusement rejeté
- Migrations 001 + 003 appliquées sur BDD locale (002 était déjà là)
- `schema.sql` mis à jour : ENUMs complets, `is_auto_rc` sur claims, table `claim_steps`
- **À faire en prod** : exécuter les 3 migrations (voir section ci-dessous)

---

### BLOCs précédents (terminés)

#### BLOC 2 — Code PIN client
- PIN initial `12345678` (hashé bcrypt) + `must_change_password=1` à la création
- Validation `^[0-9]{4,8}$` sur changePassword et resetPassword
- Anti-bruteforce : 5 tentatives / blocage 15 min (`Auth::attempt`)

#### BLOC 3 — Contrat : suppression mise en forme conditionnelle
- Carte "Documents du contrat" : neutre, sans border-danger ni bg-danger
- Types requis : mention `(requis)` en texte, sans couleur

#### BLOC 4 — Sinistres : upload client dans Correspondances
- `DocumentController::CLIENT_DOC_TYPES` : `courrier_client => correspondances`

#### BLOC 5 — Page Paramètres de compte (`/account`)
- `AccountController` : `show()` + `changePin()`
- Carte d'assurance : affichage inline image + bouton Plein écran + Télécharger

#### BLOC 6 — Admin : carte d'assurance par client
- `Document::carteAssurance()` / `archiveCarteAssurance()` — une seule carte active par client

#### BLOC 7 — Logos SVG
- `public/logoparapluie.svg` : pictogramme parapluie (bleu, monochrome) → favicon + logo unique
- `logoblanc.svg` / `logobleu.svg` conservés dans public/ mais plus utilisés dans les vues

---

## Migrations BDD — état

| Migration | BDD locale | BDD prod (à vérifier) |
|---|---|---|
| migration_001 (catégories sinistre) | ✅ appliquée | ❓ à exécuter |
| migration_002 (claim_steps, is_auto_rc) | ✅ appliquée | ❓ à exécuter |
| migration_003 (scope+category 'carte') | ✅ appliquée | ❌ **OBLIGATOIRE** |
| migration_004 (payments + scope 'paiement') | ❓ à exécuter | ❌ **OBLIGATOIRE** |

### Commandes prod
```bash
mysql -u tilki_user -pTilkiDB_2024! tilki_portal < database/migration_001_sinistre_categories.sql
mysql -u tilki_user -pTilkiDB_2024! tilki_portal < database/migration_002_claim_steps.sql
mysql -u tilki_user -pTilkiDB_2024! tilki_portal < database/migration_003_carte_assurance.sql
mysql -u tilki_user -pTilkiDB_2024! tilki_portal < database/migration_004_payments.sql
# migration_002 peut retourner "Duplicate column" si déjà appliquée → ignorer
```

---

## Architecture des fichiers importants

```
TilkiPlateforme/
├── app/
│   ├── Controllers/
│   │   ├── AccountController.php          ← paramètres client (PIN + carte)
│   │   ├── AdminClaimController.php       ← edit + updateStep (hidden inputs)
│   │   ├── AdminClientController.php      ← showEdit + uploadCarte
│   │   ├── AdminDocumentController.php    ← upload + validate (pas de refus encore)
│   │   ├── ClaimController.php
│   │   ├── ContractController.php
│   │   ├── DocumentController.php         ← download + view (inline) + upload client
│   │   └── TallyClaimWebhookController.php
│   ├── Models/
│   │   ├── Client.php
│   │   ├── Claim.php
│   │   ├── ClaimStep.php
│   │   ├── Contract.php
│   │   └── Document.php                   ← carteAssurance, archiveCarteAssurance
│   ├── Services/
│   │   ├── Auth.php                       ← anti-bruteforce clients
│   │   ├── AuditLogger.php
│   │   ├── ContractDocTypes.php
│   │   ├── Database.php
│   │   ├── FileStorage.php
│   │   └── TallyUrlBuilder.php
│   └── Views/
│       ├── account/settings.php           ← carte + PIN
│       ├── admin/claims/form.php          ← edit sinistre + steps (form dans td)
│       ├── admin/clients/edit.php         ← fiche client + upload carte
│       ├── admin/contracts/form.php
│       ├── admin/documents/pending.php    ← valider (refus pas encore implémenté)
│       ├── admin/layout/header.php        ← logoparapluie blanc (filter CSS)
│       ├── auth/login.php                 ← logoparapluie bleu h=64
│       ├── claims/show.php
│       ├── contracts/show.php
│       └── layout/header.php             ← logoparapluie blanc (filter CSS)
├── database/
│   ├── schema.sql                         ← mis à jour (ENUMs complets + claim_steps)
│   ├── migration_001_sinistre_categories.sql
│   ├── migration_002_claim_steps.sql
│   └── migration_003_carte_assurance.sql
├── public/
│   ├── index.php                          ← router
│   └── logoparapluie.svg                  ← logo unique (bleu monochrome)
└── config/config.php
```

---

## Tâches restantes

1. **Exécuter migrations 001-004** sur la base de production
2. **URL app** (`config['app']['url']`) : remplacer `https://VOTRE_DOMAINE` par le vrai domaine
3. **Refus de documents** : bouton "Refuser" dans `admin/documents/pending.php` + route + méthode `AdminDocumentController::reject()`
4. **Notification email** au client lors de la validation d'un document
5. **Pagination** sur la liste des sinistres
