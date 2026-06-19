<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réinitialisation du code PIN &ndash; TILKI</title>
    <link rel="icon" type="image/svg+xml" href="/logoparapluie.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page d-flex align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-6 col-lg-4">
            <div class="card login-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-key text-primary" style="font-size:3rem"></i>
                        <h1 class="h4 mt-2 fw-bold">Nouveau code PIN</h1>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small">
                            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
                            <?php if (empty($token)): ?>
                                <div class="mt-2">
                                    <a href="/password/forgot">Faire une nouvelle demande</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($token)): ?>
                        <form method="post" action="/password/reset" novalidate>
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nouveau code PIN</label>
                                <input type="password" name="new_password" class="form-control"
                                       inputmode="numeric" pattern="[0-9]{4,8}"
                                       minlength="4" maxlength="8"
                                       autocomplete="new-password" required autofocus>
                                <div class="form-text">Entre 4 et 8 chiffres uniquement.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Confirmer le code PIN</label>
                                <input type="password" name="confirm_password" class="form-control"
                                       inputmode="numeric" pattern="[0-9]{4,8}"
                                       minlength="4" maxlength="8"
                                       autocomplete="new-password" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-check2-circle me-2"></i>Enregistrer le code PIN
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="/login" class="small text-muted">
                            <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
