<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion &ndash; TILKI</title>
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
                        <i class="bi bi-shield-check text-primary" style="font-size:3.5rem"></i>
                        <h1 class="h3 mt-2 fw-bold">TILKI</h1>
                        <p class="text-muted small mb-0">Portail Client &mdash; Courtage en assurance</p>
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
                                Numéro de compte
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" id="account_number" name="account_number"
                                       class="form-control" placeholder="000000"
                                       maxlength="6" pattern="\d{6}" inputmode="numeric"
                                       value="<?= htmlspecialchars($account_number ?? '') ?>"
                                       required autofocus>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold" for="password">
                                Mot de passe
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password"
                                       class="form-control" required>
                            </div>
                        </div>

                        <div class="text-end mb-4">
                            <a href="/password/forgot" class="small text-muted">
                                Mot de passe oublié ?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
