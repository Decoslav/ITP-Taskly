<?php
    $servername = "localhost";
    $username = "root"; // Dein Datenbank-Benutzername
    $password = ""; // Dein Datenbank-Passwort (falls vorhanden)
    $dbname = "webseite_db"; // Name deiner Datenbank

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }
?>
