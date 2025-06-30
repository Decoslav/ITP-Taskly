<?php include './includes/header.php'; ?>

<?php
    include './DB/db.php'; // Verbindung zur Datenbank

    $message = "";

    // Prüfen, ob das Formular gesendet wurde
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $benutzername = trim($_POST['username']);
        $passwort = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT id, passwort_hash FROM benutzer WHERE benutzername = ?");
        $stmt->bind_param("s", $benutzername);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hash);
            $stmt->fetch();
            
            if (password_verify($passwort, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $benutzername;
                header("Location: index.php");
                exit();
            } else {
                $message = "Falsches Passwort!";
            }
        } else {
            $message = "Benutzer nicht gefunden!";
        }
        $stmt->close();
    }
    $conn->close();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h2 class="text-center">Login</h2>
            <?php if ($message): ?>
                <div class="alert alert-danger"><?= $message ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="login-username" class="form-label">Benutzername</label>
                    <input type="text" class="form-control" id="login-username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="login-password" class="form-label">Passwort</label>
                    <input type="password" class="form-control" id="login-password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Einloggen</button>
            </form>
            <p class="mt-3 text-center">
                Noch keinen Account? <a href="register.php">Registrieren</a>
            </p>
        </div>
    </div>
</div>

