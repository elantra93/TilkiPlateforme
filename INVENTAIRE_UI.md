# INVENTAIRE FONCTIONNEL — TILKIplateformeV3
> Généré le 2026-06-26 depuis le code source. Destiné à la conception des wireframes.  
> Répertoire : `/root/TILKIplateformeV3` — Stack : PHP 8.3 / MySQL 8 / Bootstrap 5.3

---

## 1. Cartographie des écrans / routes

### 1.1 ESPACE PUBLIC (non authentifié)

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/` | GET | `auth/login.php` | Redirection vers login |
| `/login` | GET | `auth/login.php` | Formulaire connexion client |
| `/login` | POST | — | Traitement connexion |
| `/logout` | GET/POST | — | Déconnexion client |
| `/password/forgot` | GET | `auth/forgot_password.php` | Demande réinitialisation MDP |
| `/password/forgot` | POST | — | Envoi email reset |
| `/password/reset` | GET | `auth/reset_password.php` | Formulaire nouveau MDP (via token) |
| `/password/reset` | POST | — | Traitement reset MDP |
| `/webhooks/tally` | POST | — | Webhook Tally (devis/soumissions) |
| `/webhooks/tally-sinistre` | POST | — | Webhook Tally (déclarations sinistres) |

---

### 1.2 ESPACE CLIENT (auth : compte + PIN)

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/dashboard` | GET | `dashboard/index.php` | Tableau de bord : contrats actifs + sinistres ouverts + alerte solde dû |
| `/contracts` | GET | `contracts/index.php` | Liste des contrats du client |
| `/contracts/{id}` | GET | `contracts/show.php` | Détail contrat : infos, docs cotation/souscription, historique paiements, formulaire règlement |
| `/contracts/{id}/payment` | POST | — | Soumettre une preuve de règlement (client → en attente validation) |
| `/contracts/{id}/upload` | POST | — | Upload document sur un contrat |
| `/claims` | GET | `claims/index.php` | Liste de tous les sinistres |
| `/claims/declare` | GET | `claims/declare.php` | Sélection contrat → redirection Tally (formulaire déclaration externe) |
| `/claims/{id}` | GET | `claims/show.php` | Détail sinistre : frise avancement + docs par catégorie + upload client |
| `/claims/{id}/upload` | POST | — | Upload document sur un sinistre |
| `/payments` | GET | `payments/index.php` | Historique de tous les paiements du client |
| `/devis` | GET | `devis/index.php` | Grille de branches → lien Tally formulaire devis (filtré par `account_type`) |
| `/documents/{id}/download` | GET | — | Téléchargement sécurisé d'un document |
| `/documents/{id}/view` | GET | — | Affichage inline (PDF/image) |
| `/account` | GET | `account/settings.php` | Carte d'assurance (aperçu + DL) + changement PIN |
| `/account/pin` | POST | — | Traitement changement PIN |
| `/password/change` | GET | `auth/change_password.php` | Changement MDP forcé (1ère connexion) |
| `/password/change` | POST | — | Traitement changement MDP |

---

### 1.3 ESPACE ADMIN (auth : email + MDP)

#### Auth & Dashboard

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/admin` | GET | — | Redirection `/admin/dashboard` |
| `/admin/login` | GET | `admin/login.php` | Connexion admin |
| `/admin/login` | POST | — | Traitement connexion |
| `/admin/logout` | GET/POST | — | Déconnexion |
| `/admin/dashboard` | GET | `admin/dashboard.php` | KPIs (clients / contrats / sinistres / docs) + dernières tentatives de connexion |
| `/admin/password/change` | GET | `admin/change_password.php` | Changement MDP admin |
| `/admin/password/change` | POST | — | Traitement |

#### Clients

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/admin/clients` | GET | `admin/clients/index.php` | Liste clients (tableau cliquable) |
| `/admin/clients/create` | GET | `admin/clients/create.php` | Formulaire création client + affichage identifiants générés |
| `/admin/clients/create` | POST | — | Création : génère N° compte + PIN 12345678 |
| `/admin/clients/{id}/edit` | GET | `admin/clients/edit.php` | Fiche client : infos + carte assurance + docs dossier client |
| `/admin/clients/{id}/carte` | POST | — | Upload/remplacement carte d'assurance |
| `/admin/clients/{id}/upload-doc` | POST | — | Upload document dossier client |

#### Contrats

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/admin/contracts` | GET | `admin/contracts/index.php` | Liste contrats |
| `/admin/contracts/create` | GET | `admin/contracts/form.php` | Nouveau contrat |
| `/admin/contracts/create` | POST | — | Création |
| `/admin/contracts/{id}/edit` | GET | `admin/contracts/form.php` | Édition contrat + section docs + section paiements |
| `/admin/contracts/{id}/edit` | POST | — | Sauvegarde modifications |
| `/admin/contracts/{id}/upload` | POST | — | Upload doc contrat (cotation/souscription) |
| `/admin/contracts/{id}/payment` | POST | — | Enregistrement paiement rapide depuis fiche contrat |
| `/admin/contracts/{id}/payment/{pid}/validate` | POST | — | Validation paiement en attente |

#### Sinistres

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/admin/claims` | GET | `admin/claims/index.php` | Liste sinistres (paginée 20/page) |
| `/admin/claims/create` | GET | `admin/claims/form.php` | Nouveau sinistre |
| `/admin/claims/create` | POST | — | Création |
| `/admin/claims/tally-redirect` | GET | `admin/claims/tally.php` | Redirection vers Tally (déclaration sinistre admin) |
| `/admin/claims/{id}/edit` | GET | `admin/claims/form.php` | Édition sinistre + docs + frise étapes |
| `/admin/claims/{id}/edit` | POST | — | Sauvegarde |
| `/admin/claims/{id}/steps/{sid}` | POST | — | Mise à jour étape d'avancement |
| `/admin/claims/{id}/upload` | POST | — | Upload doc sur sinistre |

#### Paiements

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/admin/payments` | GET | `admin/payments/index.php` | Liste globale tous paiements (tous clients) |
| `/admin/payments/create` | GET | `admin/payments/create.php` | Formulaire paiement dédié (saisie complète) |
| `/admin/payments/create` | POST | — | Création paiement |

#### Documents

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/admin/documents/pending` | GET | `admin/documents/pending.php` | File documents en attente de validation (tous clients) |
| `/admin/documents/{id}/preview` | GET | — | Prévisualisation sécurisée (modale) |
| `/admin/documents/{id}/download` | GET | — | Téléchargement sécurisé admin |
| `/admin/documents/{id}/validate` | POST | — | Validation document → statut `valide` |
| `/admin/documents/{id}/reject` | POST | — | Refus document + email client |
| `/admin/documents/upload` | GET/POST | — | Route obsolète → redirige vers `/admin/documents/pending` |

#### File Tally / Devis / Admins

| URL | Méthode | Vue | But |
|-----|---------|-----|-----|
| `/admin/tally/queue` | GET | `admin/tally/queue.php` | File de soumissions Tally non rattachées (pending/matched/ignored) |
| `/admin/tally/{id}/match` | POST | — | Rattacher soumission à un client |
| `/admin/tally/{id}/ignore` | POST | — | Ignorer soumission |
| `/admin/devis` | GET | `admin/devis/index.php` | Vue admin grille devis (liens Tally par branche) |
| `/admin/admins` | GET | `admin/admins/index.php` | Liste des comptes admin (superadmin uniquement) |
| `/admin/admins/create` | GET/POST | `admin/admins/form.php` | Créer compte admin |
| `/admin/admins/{id}/edit` | GET/POST | `admin/admins/form.php` | Modifier compte admin |

---

## 2. Modèle de données

> **Note** : Le `schema.sql` est la base ; les migrations 006-009 ont ajouté des colonnes non encore reflétées dans ce fichier (voir §6).

### Table `clients`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `account_number` | CHAR(6) UNIQUE | N° à 6 chiffres, séquence annuelle suggérée |
| `first_name` | VARCHAR(100) | |
| `last_name` | VARCHAR(100) | |
| `email` | VARCHAR(191) UNIQUE | |
| `phone` | VARCHAR(30) NULL | |
| `password_hash` | VARCHAR(255) | bcrypt, appelé "PIN" dans l'UI |
| `must_change_password` | TINYINT(1) | 1 = changement forcé à la 1ère connexion |
| `reset_token` | VARCHAR(100) NULL | |
| `reset_token_expires` | DATETIME NULL | |
| **`status`** | **ENUM** | `actif` / `inactif` / `suspendu` |
| **`account_type`** | **ENUM** | `individuel` / `entreprise` _(migration 006)_ |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

### Table `contracts`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `client_id` | INT UNSIGNED FK→clients | CASCADE DELETE |
| `branche` | VARCHAR(100) | Valeur de `Branches::BRANCHES` |
| `policy_number` | VARCHAR(100) | N° de police |
| `insurer` | VARCHAR(150) | Valeur de `Branches::INSURERS` |
| `effective_date` | DATE | Date d'effet |
| `expiry_date` | DATE | Date d'échéance |
| `emission_date` | DATE NULL | Date d'émission _(migration 007)_ |
| `premium_total` | DECIMAL(12,2) | Prime totale saisie |
| `premium_net` | DECIMAL(12,2) | Prime nette _(migration 007)_ |
| `premium_fees` | DECIMAL(12,2) | Frais & taxes _(migration 007)_ |
| `premium_due` | DECIMAL(12,2) | Restant dû **calculé** (premium_total − Σ paiements validés) |
| `currency` | CHAR(3) | Défaut `XOF` |
| **`status`** | **ENUM** | `actif` / `expiré` / `résilié` / `suspendu` |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

### Table `claims`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `client_id` | INT UNSIGNED FK→clients | |
| `contract_id` | INT UNSIGNED FK→contracts NULL | Optionnel |
| `claim_number` | VARCHAR(100) | N° sinistre |
| `insurer` | VARCHAR(150) | Texte libre (≠ select contrat) |
| `branche` | VARCHAR(100) | Texte libre (≠ select contrat) |
| `occurrence_date` | DATE | Date de survenance |
| **`status`** | **ENUM** | `ouvert` / `clos` |
| `description` | TEXT NULL | |
| `is_auto_rc` | TINYINT(1) | Sinistre auto en RC |
| `created_at` / `updated_at` | DATETIME | |

### Table `claim_steps`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `claim_id` | INT UNSIGNED FK→claims | CASCADE DELETE |
| `step_key` | VARCHAR(50) | Identifiant logique de l'étape |
| `label` | VARCHAR(255) | Libellé affiché (frise avancement) |
| `position` | TINYINT UNSIGNED | Ordre d'affichage |
| `completed` | TINYINT(1) | |
| `completed_date` | DATE NULL | |

### Table `documents`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `client_id` | INT UNSIGNED FK→clients | |
| `contract_id` | INT UNSIGNED FK→contracts NULL | |
| `claim_id` | INT UNSIGNED FK→claims NULL | |
| **`scope`** | **ENUM** | `contrat` / `sinistre` / `carte` / `paiement` / `client` |
| **`category`** | **ENUM** | `cotation` / `souscription` / `declaration` / `expertise_devis` / `correspondances` / `reglements_remboursements` / `carte` / `paiement` / `client` |
| `doc_type` | VARCHAR(100) | Clé du type (voir §3) |
| `original_filename` | VARCHAR(255) | |
| `stored_path` | VARCHAR(500) | Chemin opaque (hors web) |
| `mime_type` | VARCHAR(100) | |
| `file_size` | INT UNSIGNED | Octets |
| **`source`** | **ENUM** | `admin` / `tally` / `client` |
| **`status`** | **ENUM** | `valide` / `en_attente` |
| `created_at` | DATETIME | |

### Table `payments`

> Schéma post-migration 008 (la table initiale créée par migration_004 a été restructurée).

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `client_id` | INT UNSIGNED FK→clients | |
| `contract_id` | INT UNSIGNED FK→contracts | |
| `amount` | DECIMAL(12,2) | 0.00 pour les paiements migrés depuis scope=paiement |
| **`method`** | **ENUM** | `especes` / `virement` / `cheque` / `caisse` / `mobile_money` / `carte` |
| `document_id` | INT UNSIGNED FK→documents NULL | Preuve (ex `proof_document_id`) |
| **`status`** | **ENUM** | `en_attente` / `valide` |
| `validated_by` | INT NULL | ID admin ayant validé |
| `validated_at` | DATETIME NULL | |
| `reference` | VARCHAR(100) NULL | |
| `paid_at` | DATE NULL | |
| `note` | TEXT NULL | Note interne admin |
| **`created_by`** | **ENUM** | `admin` / `client` |
| `created_at` | TIMESTAMP | |

### Table `tally_queue`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `event_id` | VARCHAR(100) | |
| `response_id` | VARCHAR(100) UNIQUE | |
| `form_id` / `form_name` | VARCHAR | |
| `payload` | JSON | Réponses brutes du formulaire Tally |
| **`status`** | **ENUM** | `pending` / `matched` / `ignored` |
| `client_id` | INT UNSIGNED FK→clients NULL | Renseigné après rattachement |

### Table `admins`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `email` | VARCHAR(191) UNIQUE | |
| `password_hash` | VARCHAR(255) | |
| `name` | VARCHAR(150) | |
| **`role`** | **ENUM** | `superadmin` / `admin` / `support` |

### Table `login_attempts`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | INT UNSIGNED PK | |
| `identifier` | VARCHAR(20) | N° compte ou email |
| `ip` | VARCHAR(45) | |
| `success` | TINYINT(1) | |
| `created_at` | DATETIME | |

> Règle : 5 échecs sur le même identifiant → blocage 15 min.

### Table `audit_log`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | BIGINT PK | |
| `actor_type` | ENUM | `client` / `admin` / `system` |
| `actor_id` | INT NULL | |
| `action` | VARCHAR(100) | Ex : `client_created`, `carte_uploaded`, `doc_validated` |
| `target` | VARCHAR(255) | Ex : `client:42`, `document:7` |
| `ip` | VARCHAR(45) | |

### Relations synthétiques

```
clients ──< contracts ──< payments
       ──< claims ──< claim_steps
       ──< documents >── contracts (optional)
                     >── claims    (optional)
payments >── documents (proof, optional)
tally_queue >── clients (optional, after match)
```

---

## 3. Types de documents

### 3.1 Scope `carte` — Carte d'assurance client

| `doc_type` | Libellé | Uploadé par |
|------------|---------|-------------|
| `carte_assurance` | Carte d'assurance | Admin uniquement |

> Un seul fichier actif par client (l'ancien est archivé à chaque remplacement).  
> Formats : PDF, JPG, PNG — 10 Mo max.

---

### 3.2 Scope `client` — Dossier client

| `doc_type` | Libellé |
|------------|---------|
| `cni` | Carte Nationale d'Identité |
| `passeport` | Passeport |
| `permis_conduire` | Permis de conduire |
| `justificatif_domicile` | Justificatif de domicile |
| `formulaire` | Formulaire de souscription |
| `autre` | Autre document |

> Source : admin uniquement. Statut : `valide` (auto à l'upload).

---

### 3.3 Scope `contrat` — Documents contrat

Deux catégories avec cascade branche :

**Category `cotation`** (documents avant souscription) :

| `doc_type` | Libellé |
|------------|---------|
| `questionnaire` | Questionnaire |
| `cotation` | Cotation |
| `bordereau` | Bordereau |
| `note_de_couverture` | Note de couverture |

**Category `souscription`** — par branche :

| Branche | `doc_type` | Libellé | Requis |
|---------|-----------|---------|--------|
| **Automobile** | `conditions_particulieres` | Conditions particulières | Oui |
| | `attestation_assurance` | Attestation d'assurance | Oui |
| | `attestation_cedeao` | Attestation CEDEAO | Oui |
| | `conditions_generales` | Conditions générales | Non |
| **Santé** | `contrat` | Contrat | Oui |
| | `tableau_garanties` | Tableau de garanties | Oui |
| | `reseau_soins` | Réseau de soins | Oui |
| **Autres branches** (générique) | `contrat` | Contrat | — |
| | `avenant` | Avenant | — |
| | `preuve_paiement` | Preuve de paiement | — |
| | `quittance` | Quittance | — |
| | `attestation` | Attestation | — |
| | `decompte` | Décompte | — |

> L'admin voit une checklist avec badge "N/A" sur les types non applicables.

---

### 3.4 Scope `sinistre` — Documents sinistre

| Category | `doc_type` | Libellé | Uploadable par |
|----------|-----------|---------|----------------|
| `declaration` | `declaration_sinistre` | Déclaration de sinistre | Client + Admin |
| | `courrier_client` | Courrier / document divers | Client |
| | *(Tally)* | Réponses formulaire Tally | Automatique (webhook) |
| `expertise_devis` | `devis_reparation` | Devis | Client + Admin |
| | `rapport_expertise` | Rapport d'expertise | Client + Admin |
| | `constat_police` | Constat de police | Client + Admin |
| `correspondances` | `courrier_client` | Courrier/correspondance | Client + Admin |
| | *(libre)* | Tout type libre | Admin |
| `reglements_remboursements` | *(libre)* | Règlements / remboursements | Admin uniquement (lecture seule côté client) |

---

### 3.5 Scope `paiement` — Legacy

> Scope existant en BDD (`documents.scope = 'paiement'`) mais logiquement migré vers la table `payments` via migration_009. Les documents restent en base mais sont maintenant référencés via `payments.document_id`.

---

## 4. Formulaires clés

### 4.1 Déclaration de sinistre

**Vue client** (`/claims/declare`) — Ce n'est PAS un formulaire d'application. C'est un écran de sélection qui redirige vers un formulaire Tally externe.

| Champ | Type | Obligatoire |
|-------|------|:-----------:|
| Contrat concerné | Select (liste des contrats du client) | Oui |

> Action : bouton "Accéder au formulaire" → ouvre Tally (`TALLY_CLAIM_FORM_URL`) dans un nouvel onglet, avec les données du contrat pré-remplies via `TallyUrlBuilder`.

**Vue admin** (`/admin/claims/create` et `/admin/claims/{id}/edit`) :

| Champ | Type HTML | Obligatoire | Valeurs |
|-------|-----------|:-----------:|---------|
| Client | Select | Oui (create) / lecture seule (edit) | Liste `account_number — Prénom Nom` |
| Contrat lié | Select (cascade depuis client) | Non | Liste polices du client |
| N° Sinistre | Text | Oui | Libre |
| Assureur | Text | Oui | Libre (pas de select) |
| Branche | Text | Oui | Libre (pas de select) |
| Date de survenance | Date | Oui | |
| Statut | Select | Non | `ouvert` / `clos` (défaut : ouvert) |
| Sinistre RC auto | Checkbox (switch) | Non | Boolean |
| Description | Textarea (3 lignes) | Non | |

**Upload docs sinistre (admin, dans fiche édition)** :

| Champ | Type | Obligatoire |
|-------|------|:-----------:|
| Catégorie | Select | Oui | `declaration` / `expertise_devis` / `correspondances` / `reglements_remboursements` |
| Type de document | Select (cascade JS depuis catégorie) | Oui |
| Fichier | File (PDF, image, Word, Excel ≤ 10 Mo) | Oui |

---

### 4.2 Enregistrement d'un paiement

#### Formulaire admin dédié (`/admin/payments/create`)

| Champ | Type HTML | Obligatoire | Notes |
|-------|-----------|:-----------:|-------|
| Client | Select | Oui | `account_number — Prénom Nom` |
| Contrat | Select (cascade JS depuis client) | Oui | |
| Montant (XOF) | Number (min 1, step 1) | Oui | |
| Date de paiement | Date | Oui | Défaut : aujourd'hui |
| Mode de paiement | Select | Oui | `cheque` / `virement bancaire` / `caisse` / `mobile_money` |
| Référence | Text (max 100) | Non | N° chèque, reçu, transaction… |
| Preuve de paiement | File (PDF, JPG, PNG ≤ 10 Mo) | Non | |
| Note interne | Textarea (max 1000) | Non | |

#### Formulaire rapide admin (dans fiche contrat)

| Champ | Obligatoire |
|-------|:-----------:|
| Montant | Oui |
| Mode (select) | Oui |
| Date de paiement | Non (défaut : aujourd'hui) |
| Référence | Non |
| Justificatif (file) | Non |

#### Formulaire client (dans détail contrat `/contracts/{id}`)

| Champ | Type HTML | Obligatoire | Notes |
|-------|-----------|:-----------:|-------|
| Montant | Number (min 1) | Oui | Affiché avec devise du contrat |
| Mode de paiement | Select | Oui | `especes` / `virement` / `cheque` / `mobile_money` / `carte` |
| Justificatif | File (PDF, JPG, PNG ≤ 10 Mo) | Oui | |

> Le paiement client part en statut `en_attente`. Pas de note ni de référence côté client.

**Modes de paiement disponibles par contexte :**

| Mode | Label | Admin dédié | Admin rapide | Client |
|------|-------|:-----------:|:------------:|:------:|
| `cheque` | Chèque | ✓ | ✓ | ✓ |
| `virement` | Virement bancaire | ✓ | ✓ | ✓ |
| `caisse` | Caisse | ✓ | ✓ | — |
| `mobile_money` | Mobile Money | ✓ | ✓ | ✓ |
| `especes` | Espèces | — | — | ✓ |
| `carte` | Carte | — | — | ✓ |

---

### 4.3 Création / édition de contrat (`/admin/contracts/create` et `.../edit`)

| Champ | Type HTML | Obligatoire | Valeurs |
|-------|-----------|:-----------:|---------|
| Client | Select | Oui (create) / lecture seule (edit) | `account_number — Prénom Nom` |
| Branche | Select | Oui | Voir §5 — 7 branches |
| N° Police | Text (monospace) | Oui | Libre |
| Assureur | Select | Oui | Voir §5 — 24 assureurs |
| Date d'émission | Date | Non | _(migration 007)_ |
| Date d'effet | Date | Oui | |
| Date d'échéance | Date | Oui | |
| Prime nette | Number (≥0, step 0.01) | Non | _(migration 007)_ |
| Frais & taxes | Number (≥0, step 0.01) | Non | _(migration 007)_ |
| Prime totale | Number (≥0, step 0.01) | Non | |
| Restant dû | Lecture seule | — | Calculé : prime_total − Σ paiements validés |
| Devise | Text (max 3) | Non | Défaut `XOF` |
| Statut | Select | Non | `actif` / `expiré` / `résilié` / `suspendu` (défaut : actif) |

> En mode édition, la fiche affiche en dessous : section documents (checklist branche) + section paiements.

---

### 4.4 Création client (`/admin/clients/create`)

| Champ | Type HTML | Obligatoire | Notes |
|-------|-----------|:-----------:|-------|
| Prénom | Text | Oui | |
| Nom | Text | Oui | |
| Email | Email | Oui | |
| Téléphone | Tel | Non | |
| Statut | Select | Non | `actif` / `inactif` / `suspendu` (défaut : actif) |
| N° de compte | Text (6 chiffres) | Oui | Auto-suggéré (séquence annuelle), modifiable |

> Après création : le PIN initial `12345678` est affiché une seule fois avec bouton copier.

---

### 4.5 Création / édition compte admin (`/admin/admins/create` et `.../edit`)

| Champ | Type HTML | Obligatoire | Notes |
|-------|-----------|:-----------:|-------|
| Nom complet | Text | Oui | |
| Adresse e-mail | Email | Oui | |
| Rôle | Select | Oui | `superadmin` / `admin` / `support` |
| Mot de passe | Password (min 8) | Oui (create) / Non (edit) | |
| Confirmer MDP | Password | Oui (create) | |
| Nouveau MDP | Password (min 8) | Non (edit) | Laisser vide = conserver |

---

## 5. Référentiel assureurs

### Branches disponibles (`Branches::BRANCHES`)

| # | Branche | Icône UI | Types compte |
|---|---------|----------|-------------|
| 1 | Automobile | `bi-car-front-fill` | individuel + entreprise |
| 2 | Moto | `bi-bicycle` | individuel + entreprise |
| 3 | Assurance voyage | `bi-airplane-fill` | individuel + entreprise |
| 4 | Assurance santé | `bi-heart-pulse-fill` | individuel + entreprise |
| 5 | Multirisques habitation | `bi-house-fill` | individuel uniquement |
| 6 | Multirisques professionnelle | `bi-building-fill` | entreprise uniquement |
| 7 | Responsabilité civile | `bi-shield-check-fill` | entreprise uniquement |

### Compagnies d'assurance (`Branches::INSURERS`) — 24 entrées

| # | Nom |
|---|-----|
| 1 | ALLIANZ Sénégal |
| 2 | AMSA Assurances |
| 3 | ASKIA Assurances |
| 4 | ATLANTIQUE Assurances |
| 5 | AXA Assurances Sénégal |
| 6 | BAVIC Assurances |
| 7 | Compagnie Africaine d'Assurances (CAA) |
| 8 | CNAAS |
| 9 | GFA Assurances |
| 10 | LAFIA Assurances |
| 11 | Mutuelle Panafricaine de Garantie (MPG) |
| 12 | NSIA Sénégal |
| 13 | PRUDENTIAL Africa Sénégal |
| 14 | SAHAM Assurance Sénégal |
| 15 | SAAR Assurance |
| 16 | SALAMA Assurances |
| 17 | SANLAM Sénégal |
| 18 | SMIG |
| 19 | SONAR Sénégal |
| 20 | SUNU Assurances IARD Sénégal |
| 21 | SUNU Assurances Vie Sénégal |
| 22 | UAB Sénégal |
| 23 | Wafa Assurance Sénégal |
| 24 | NOVELIA Gestion Santé _(ajouté commit 6ae141f)_ |

---

## 6. Écarts et zones non finalisées

### 6.1 Schema.sql désynchronisé des migrations

Le fichier `database/schema.sql` ne reflète pas les 4 dernières migrations. Pour un déploiement propre, il faudrait le régénérer. Colonnes manquantes :

| Table | Colonne manquante | Migration source |
|-------|-------------------|-----------------|
| `clients` | `account_type ENUM('individuel','entreprise')` | migration_006 |
| `contracts` | `emission_date`, `premium_net`, `premium_fees` | migration_007 |
| `payments` | `document_id` (renommé depuis `proof_document_id`) | migration_008 |
| `payments` | `status ENUM('en_attente','valide')` | migration_008 |
| `payments` | `validated_by`, `validated_at` | migration_008 |
| `payments` | `created_by ENUM('admin','client')` | migration_008 |
| `payments` | ENUM `method` étendu à `especes` et `carte` | migration_008 |

### 6.2 Statut `rejeté` sur payments — incohérence code/BDD

Le PHP affiche un badge "Rejeté" (`'rejeté' => 'bg-danger'`) dans la fiche contrat admin, mais l'ENUM MySQL de `payments.status` ne contient que `en_attente` / `valide`. La valeur `rejeté` ne peut jamais exister en BDD avec le schéma actuel.

**Fichier concerné** : `app/Views/admin/contracts/form.php:451`

### 6.3 URLs formulaires devis Tally non configurées

Les 7 branches de devis (`TALLY_DEVIS_AUTO`, `TALLY_DEVIS_MOTO`, `TALLY_DEVIS_VOYAGE`, `TALLY_DEVIS_SANTE`, `TALLY_DEVIS_MRH`, `TALLY_DEVIS_MRP`, `TALLY_DEVIS_RC`) sont lues depuis `.env`. Toute branche sans URL affiche "Bientôt disponible" côté client. État de configuration en prod : non vérifié dans le code.

### 6.4 Champs assureur et branche libres sur les sinistres

Le formulaire admin de sinistre utilise des champs **texte libre** pour `insurer` et `branche`, alors que le formulaire de contrat utilise des selects alimentés par `Branches::INSURERS` et `Branches::BRANCHES`. Risque de valeurs hétérogènes en BDD.

### 6.5 Scope `paiement` en double

La table `documents` conserve le scope `paiement` dans son ENUM, mais la logique métier est désormais dans la table `payments` (migration_009 migre les anciens docs). Ce scope est potentiellement orphelin selon l'état des données en prod.

### 6.6 Modes de paiement incohérents entre formulaires

Les formulaires admin et client n'exposent pas les mêmes modes (voir tableau §4.2). Notamment `especes` et `carte` sont absents des formulaires admin, et `caisse` est absent du formulaire client. L'ENUM BDD inclut les 6 valeurs.

### 6.7 Validation paiement — page dédiée manquante

Il n'existe pas de route GET dédiée à la liste "paiements en attente de validation" (équivalent de `/admin/documents/pending` pour les paiements). La validation se fait uniquement depuis la fiche contrat individuelle via le bouton "Vérifier" (modale).

### 6.8 `steps` sinistre — création d'étapes non exposée en UI

Les étapes (`claim_steps`) sont créées en BDD mais il n'existe pas de formulaire pour ajouter ou supprimer des étapes depuis l'UI. Seule la mise à jour (cocher + date) est disponible. La création initiale des étapes dépend probablement d'un mécanisme de seed non visible dans les vues.

### 6.9 Alerte expiration contrat — côté admin absente

Côté client (`dashboard/index.php`), les contrats expirant dans moins de 30 jours affichent une alerte visuelle. Cette logique n'existe pas dans les vues admin.

### 6.10 Pagination limitée aux sinistres admin

Seule la liste `/admin/claims` est paginée (20/page). Les listes clients, contrats, paiements et documents ne sont pas paginées — risque de performance sur grands volumes.
