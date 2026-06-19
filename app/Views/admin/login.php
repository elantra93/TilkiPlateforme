<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administration &ndash; TILKI</title>
    <link rel="icon" type="image/svg+xml" href="/logoparapluie.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-6 col-lg-4">
            <div class="card login-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <img src="/logoparapluie.svg" alt="TILKI" height="56" style="width:auto">
                        <p class="text-muted small mb-0 mt-2">Administration &mdash; Accès réservé</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small">
                            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/admin/login" novalidate>
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="email">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email"
                                       class="form-control"
                                       value="<?= htmlspecialchars($email ?? '') ?>"
                                       required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold" for="password">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password"
                                       class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 py-2 fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Connexion admin
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/login" class="small text-muted">
                            <i class="bi bi-arrow-left me-1"></i>Portail client
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
