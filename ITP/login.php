<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'header.php'; ?>
</head>
<body class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-4">
            <h2 class="text-center">Login</h2>
            <form>
                <div class="mb-3">
                    <label for="login-username" class="form-label">Benutzername</label>
                    <input type="text" class="form-control" id="login-username" required>
                </div>
                <div class="mb-3">
                    <label for="login-password" class="form-label">Passwort</label>
                    <input type="password" class="form-control" id="login-password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Einloggen</button>
            </form>
            <p class="mt-3 text-center">
                Noch keinen Account? <a href="register.html">Registrieren</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
