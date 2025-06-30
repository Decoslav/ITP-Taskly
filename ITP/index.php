<?php include './includes/header.php'; ?> 

<div class="container mt-5 text-center">
    <h1 class="display-4 mb-4">Willkommen bei <span class="text-primary">Taskly</span></h1>
    <p class="lead mb-5">Dein smarter Kalender für Tages-, Wochen- und Monatsplanung</p>

    <?php if (!isset($_SESSION['username'])): ?>
        <a href="register.php" class="btn btn-dark btn-lg me-2">Jetzt registrieren</a>
        <a href="login.php" class="btn btn-outline-dark btn-lg">Login</a>
    <?php else: ?>
        <a href="calendar.php" class="btn btn-success btn-lg">Zum Kalender</a>
    <?php endif; ?>
</div>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Funktionen auf einen Blick</h2>
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="p-4 bg-white shadow rounded">
                <h4>Tagesansicht</h4>
                <p>Stundenweise Planung mit Task-Übersicht und Zeitdetails.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white shadow rounded">
                <h4>Wochenansicht</h4>
                <p>Alle Tasks der Woche im Überblick – ideal zur Organisation.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white shadow rounded">
                <h4>Monatsansicht</h4>
                <p>Langfristig planen mit Task-Markierungen in der Übersicht.</p>
            </div>
        </div>
    </div>

    