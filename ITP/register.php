<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'header.php'; ?>

</head>
<body class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-4">
            <h2 class="text-center">Registrierung</h2>
            <form>
                <div class="mb-3">
                    <label for="register-username" class="form-label">Benutzername</label>
                    <input type="text" class="form-control" id="register-username" required>
                </div>
                <div class="mb-3">
                    <label for="register-email" class="form-label">E-Mail</label>
                    <input type="email" class="form-control" id="register-email" required>
                </div>
                <div class="mb-3">
                    <label for="register-password" class="form-label">Passwort</label>
                    <input type="password" class="form-control" id="register-password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Registrieren</button>
            </form>
            <p class="mt-3 text-center">
                Bereits registriert? <a href="login.php">Zum Login</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
