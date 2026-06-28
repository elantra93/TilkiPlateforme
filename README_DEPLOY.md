# Guide de déploiement — TilkiPlateforme

## Première installation sur un nouvel environnement

### 1. Cloner le dépôt

```bash
git clone -b beta https://github.com/elantra93/TilkiPlateforme.git tilki_app
```

### 2. Créer le fichier `.env`

```bash
cp tilki_app/.env.example tilki_app/.env
```

Ouvrir `.env` et renseigner toutes les valeurs :

| Variable | Description |
|---|---|
| `DB_NAME` | Nom de la base de données (Hostinger : `u123456789_nombase`) |
| `DB_USER` | Utilisateur MySQL (Hostinger : `u123456789_nomuser`) |
| `DB_PASS` | Mot de passe MySQL |
| `MAIL_SMTP_PASS` | Mot de passe du compte e-mail SMTP |
| `TALLY_SECRET` | Clé de signature Tally (webhook signing key) |
| `TALLY_CLAIM_FORM_URL` | URL du formulaire Tally de déclaration de sinistre |

> **Important** : le fichier `.env` ne doit jamais être commité ni partagé.

### 3. Configurer le serveur web

**VPS / Apache** : pointer le `DocumentRoot` vers `tilki_app/public/`.

**Hostinger (mutualisé)** :
- Déposer le contenu de `tilki_app/public/` dans `public_html/`
- Les autres dossiers (`app/`, `config/`, etc.) restent dans `tilki_app/`
- Le routeur `public/index.php` détecte automatiquement la structure sibling

### 4. Appliquer les migrations

```bash
mysql -u $DB_USER -p$DB_PASS $DB_NAME < tilki_app/database/migration_001_sinistre_categories.sql
mysql -u $DB_USER -p$DB_PASS $DB_NAME < tilki_app/database/migration_002_claim_steps.sql
mysql -u $DB_USER -p$DB_PASS $DB_NAME < tilki_app/database/migration_003_carte_assurance.sql
mysql -u $DB_USER -p$DB_PASS $DB_NAME < tilki_app/database/migration_004_payments.sql
mysql -u $DB_USER -p$DB_PASS $DB_NAME < tilki_app/database/migration_005_scope_client.sql
```

> Chaque migration est idempotente (`IF NOT EXISTS`, `IF NOT EXISTS`). Une migration
> déjà appliquée peut renvoyer un avertissement "Duplicate column" — c'est normal.

### 5. Créer les répertoires de stockage

```bash
mkdir -p tilki_app/storage/documents tilki_app/storage/logs
chmod 755 tilki_app/storage/documents tilki_app/storage/logs
```

---

## Mise à jour (déploiement continu)

```bash
git -C tilki_app pull origin beta
# Appliquer les nouvelles migrations si présentes
```

Le fichier `.env` et le dossier `storage/` ne sont pas touchés par `git pull`.

---

## Structure des secrets

```
tilki_app/
├── .env             ← créé manuellement, JAMAIS commité
├── .env.example     ← modèle versionné (sans valeurs)
├── config/
│   ├── config.php   ← versionné, lit les secrets via env()
│   └── env.php      ← versionné, chargeur .env (aucun secret)
└── ...
```
