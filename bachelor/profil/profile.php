<?php
// Hent backendlogikk
include("profile_backend.php");

// Full fysisk sti til profilbildet
$absoluteFolderPath = "/Applications/XAMPP/xamppfiles/htdocs/Bacheloroppgave/bachelor/BILDER/profilbilder/";
$webPath = "/Bacheloroppgave/bachelor/BILDER/profilbilder/";

$profileImageFile = $absoluteFolderPath . $username . ".png";
$defaultImageFile = $absoluteFolderPath . "default.png";

// Velg bilde som finnes
$imgSrc = file_exists($profileImageFile) ? $webPath . $username . ".png" : $webPath . "default.png";
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brukerprofil</title>

    <!-- Stilark og fonter -->
    <link rel="stylesheet" href="../css/profil.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">

    <!-- Tailwind og JS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="../redirectToPage.js"></script>
</head>
<body>

<!-- Header -->
<div id="header">
    <?php include("../header/header.php"); ?>
</div>

<!-- Profilkort -->
<div class="profil-container">
    <div class="profil-card">
        <div class="profil-image">
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Profilbilde" class="profil-img">
        </div>
        <div class="profil-info">
            <p>Brukernavn: <?php echo htmlspecialchars($username); ?></p>
            <p>E-post: <?php echo htmlspecialchars($epost); ?></p>
            <p>Rolle: <?php echo htmlspecialchars($rolle); ?></p>
            <br>
                <div class="profil-button">
                    <button class="secondaryBTN"><p>Endre passord</p></button>
                </div>            
        </div>
    </div>
</div>

</body>
</html>
