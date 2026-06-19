<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Code PIN oublié &ndash; TILKI</title>
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
                        <i class="bi bi-envelope-open text-primary" style="font-size:3rem"></i>
                        <h1 class="h4 mt-2 fw-bold">Code PIN oublié</h1>
                        <p class="text-muted small mb-0">Entrez votre email pour recevoir un lien de réinitialisation.</p>
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
                        <?php if (!empty($dev_url)): ?>
                            <div class="alert alert-warning py-2 small">
                                <strong>[DEV]</strong> Lien de réinitialisation :<br>
                                <a href="<?= htmlspecialchars($dev_url) ?>" class="small text-break">
                                    <?= htmlspecialchars($dev_url) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="post" action="/password/forgot" novalidate>
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                            <div class="mb-4">
                                <label class="form-label small fw-semibold" for="email">
                                    Adresse email
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="email" name="email"
                                           class="form-control" placeholder="votre@email.com"
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                           required autofocus>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-send me-2"></i>Envoyer le lien
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
