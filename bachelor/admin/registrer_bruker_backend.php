<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../db2.php");

if (!isset($_SESSION['db_username'])) {
    die("❌ Du må være logget inn som admin.");
}

// 📥 Hent data
$brukernavn = trim($_POST['brukernavn'] ?? '');
$fornavn = $_POST['fornavn'] ?? '';
$etternavn = $_POST['etternavn'] ?? '';
$telefon = $_POST['telefon'] ?? '';
$epost = $_POST['epost'] ?? '';
$bekreft_epost = $_POST['bekreft_epost'] ?? '';
$passord = $_POST['passord'] ?? '';
$bekreft_passord = $_POST['bekreft_passord'] ?? '';
$adminrettigheter = isset($_POST['adminrettigheter']);

// 👤 Registrer i user_details
try {
    $stmt = $pdo->prepare("INSERT INTO user_details (User, fornavn, etternavn, telefon, epost) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$brukernavn, $fornavn, $etternavn, $telefon, $epost]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $feil = urlencode("❌ Brukernavnet '$brukernavn' er allerede i bruk.");
        $url = "registrer_bruker(admin).php?feil=$feil"
             . "&fornavn=" . urlencode($fornavn)
             . "&etternavn=" . urlencode($etternavn)
             . "&telefon=" . urlencode($telefon)
             . "&epost=" . urlencode($epost)
             . "&brukernavn=" . urlencode($brukernavn)
             . ($adminrettigheter ? "&adminrettigheter=1" : "");
        header("Location: $url");
        exit();
    } else {
        die("❌ Feil ved lagring i user_details: " . $e->getMessage());
    }
}

// 📸 Håndter profilbilde
if (isset($_FILES['profilbilde']) && $_FILES['profilbilde']['error'] === UPLOAD_ERR_OK) {
    $tmpPath = $_FILES['profilbilde']['tmp_name'];
    $fileInfo = getimagesize($tmpPath);

    if ($fileInfo && in_array($fileInfo['mime'], ['image/png', 'image/jpeg'])) {
        $image = ($fileInfo['mime'] === 'image/png') ? imagecreatefrompng($tmpPath) : imagecreatefromjpeg($tmpPath);
        if ($image !== false) {
            $destDir = realpath(__DIR__ . '/../BILDER/profilbilder');
            $destPath = $destDir . '/' . $brukernavn . '.png';
            if (!imagepng($image, $destPath)) {
                echo "<script>alert('❌ Kunne ikke lagre bildet til disk.');</script>";
            }
            imagedestroy($image);
        }
    }
}

// 🛠️ Opprett MySQL-bruker og gi rettigheter
try {
    $pdo->exec("CREATE USER '$brukernavn'@'%' IDENTIFIED BY '$passord'");
    $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON `kunde_tabeller`.* TO '$brukernavn'@'%'");

    if ($adminrettigheter) {
        // Gi adminrolle til bruker (privilegiene finnes allerede)
        $pdo->exec("GRANT 'adminbruker' TO '$brukernavn'@'%'");
        $pdo->exec("SET DEFAULT ROLE 'adminbruker' TO '$brukernavn'@'%'");
    } else {
        // Vanlige brukere får kun SELECT på mysql
        $pdo->exec("GRANT SELECT ON `mysql`.* TO '$brukernavn'@'%'");
    }

    $pdo->exec("FLUSH PRIVILEGES");
} catch (PDOException $e) {
    die("❌ Feil ved oppretting av MySQL-bruker: " . $e->getMessage());
}

// ✅ Ferdig
header("Location: ../admin/brukeroversikt.php?registrert=1");
exit();
?>
