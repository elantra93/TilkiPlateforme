<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion &ndash; TILKI</title>
    <link rel="icon" type="image/svg+xml" href="/logoparapluie.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="tk-login-split">

<div class="tk-login-left">
    <div class="tk-login-left-inner">
        <img src="/logoblanc.svg" alt="TILKI" height="44" class="mb-4" style="width:auto">
        <h1 class="tk-login-tagline">S'assurer malin</h1>
        <p class="tk-login-sub">
            Tous vos contrats au même endroit,<br>avec un conseiller qui les connaît.
        </p>
    </div>
</div>

<div class="tk-login-right">
    <div class="tk-login-form-wrap">
        <div class="mb-4">
            <h2 class="h5 fw-bold mb-1">Bienvenue</h2>
            <p class="text-muted small mb-0">Connectez-vous à votre espace.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 small">
                <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/login" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="mb-3">
                <label class="form-label small fw-semibold" for="account_number">
                    N° de compte
                </label>
                <input type="text" id="account_number" name="account_number"
                       class="form-control" placeholder="6 chiffres"
                       maxlength="6" pattern="\d{6}" inputmode="numeric"
                       value="<?= htmlspecialchars($account_number ?? '') ?>"
                       required autofocus>
            </div>

            <div class="mb-2">
                <label class="form-label small fw-semibold" for="password">
                    Code PIN
                </label>
                <input type="password" id="password" name="password"
                       class="form-control" inputmode="numeric" required>
            </div>

            <div class="text-end mb-4">
                <a href="/password/forgot" class="small text-muted">Code oublié ?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                Se connecter
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
