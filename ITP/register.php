<?php
    session_start();
    include './DB/db.php'; // Verbindung zur Datenbank

    $message = "";

    // Prüfen, ob das Formular gesendet wurde
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $benutzername = trim($_POST['username']);
        $email = trim($_POST['email']);
        $passwort = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Überprüfung, ob Benutzer bereits existiert
        $stmt = $conn->prepare("SELECT id FROM benutzer WHERE benutzername = ? OR email = ?");
        $stmt->bind_param("ss", $benutzername, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $message = "Benutzername oder E-Mail existiert bereits!";
        } else {
            // Benutzer in die Datenbank einfügen
            $stmt = $conn->prepare("INSERT INTO benutzer (benutzername, email, passwort_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $benutzername, $email, $passwort);
            
            if ($stmt->execute()) {
                header("Location: login.php?message=Registrierung erfolgreich! Bitte logge dich ein.");
                exit();
            } else {
                $message = "Fehler bei der Registrierung.";
            }
        }
        $stmt->close();
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include './includes/header.php'; ?>
</head>
<body class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h2 class="text-center">Registrierung</h2>
            <?php if ($message): ?>
                <div class="alert alert-danger"><?= $message ?></div>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="register-username" class="form-label">Benutzername</label>
                    <input type="text" class="form-control" id="register-username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="register-email" class="form-label">E-Mail</label>
                    <input type="email" class="form-control" id="register-email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="register-password" class="form-label">Passwort</label>
                    <input type="password" class="form-control" id="register-password" name="password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Registrieren</button>
            </form>
            <p class="mt-3 text-center">
                Bereits registriert? <a href="login.php">Zum Login</a>
            </p>
        </div>
    </div>
</body>
</html>