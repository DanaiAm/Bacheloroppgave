<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../db2.php");

if (!isset($_SESSION['db_username'])) {
    die("❌ Du må være logget inn som admin.");
}

$innloggetBruker = $_SESSION['db_username'] ?? '';

// 📥 Skjemadata
$gammeltBrukernavn = trim($_POST['gammelt_brukernavn'] ?? '');
$nyttBrukernavn = trim($_POST['brukernavn'] ?? '');
$fornavn = $_POST['fornavn'] ?? '';
$etternavn = $_POST['etternavn'] ?? '';
$telefon = $_POST['telefon'] ?? '';
$epost = $_POST['epost'] ?? '';
$bekreft_epost = $_POST['bekreft_epost'] ?? '';
$nyttPassord = $_POST['passord'] ?? '';
$bekreftPassord = $_POST['bekreft_passord'] ?? '';
$adminrettigheter = isset($_POST['adminrettigheter']);

// 📸 Håndter profilbilde
if (isset($_FILES['profilbilde']) && $_FILES['profilbilde']['error'] === UPLOAD_ERR_OK) {
    $tmpPath = $_FILES['profilbilde']['tmp_name'];
    $fileInfo = getimagesize($tmpPath);

    if ($fileInfo && in_array($fileInfo['mime'], ['image/png', 'image/jpeg'])) {
        $image = null;
        if ($fileInfo['mime'] === 'image/png') {
            $image = imagecreatefrompng($tmpPath);
        } elseif ($fileInfo['mime'] === 'image/jpeg') {
            $image = imagecreatefromjpeg($tmpPath);
        }

        if ($image !== false) {
            $destDir = realpath(__DIR__ . '/../BILDER/profilbilder');
            $destPath = $destDir . '/' . $nyttBrukernavn . '.png';
            imagepng($image, $destPath);
            imagedestroy($image);
        }
    }
}

// 🔄 Oppdater brukerdata i databasen
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE user_details 
        SET User = ?, fornavn = ?, etternavn = ?, telefon = ?, epost = ?
        WHERE User = ?
    ");
    $stmt->execute([$nyttBrukernavn, $fornavn, $etternavn, $telefon, $epost, $gammeltBrukernavn]);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("❌ Feil i brukerdata: " . $e->getMessage());
}

// 🛠️ Oppdater MySQL-brukerrettigheter
try {
    if ($gammeltBrukernavn !== $nyttBrukernavn) {
        $pdo->exec("RENAME USER '$gammeltBrukernavn'@'%' TO '$nyttBrukernavn'@'%'");
    }

    if (!empty($nyttPassord)) {
        $pdo->exec("ALTER USER '$nyttBrukernavn'@'%' IDENTIFIED BY '$nyttPassord'");
    }

    // Kun endre roller hvis det ikke er deg selv
    if ($innloggetBruker !== $nyttBrukernavn) {
        try {
            $pdo->exec("REVOKE 'adminbruker' FROM '$nyttBrukernavn'@'%'");
        } catch (PDOException $e) {}

        if ($adminrettigheter) {
            $pdo->exec("GRANT 'adminbruker' TO '$nyttBrukernavn'@'%'");
            $pdo->exec("SET DEFAULT ROLE 'adminbruker' TO '$nyttBrukernavn'@'%'");
        } else {
            $pdo->exec("GRANT SELECT ON `mysql`.* TO '$nyttBrukernavn'@'%'");
            $pdo->exec("SET DEFAULT ROLE NONE TO '$nyttBrukernavn'@'%'");
        }

        $pdo->exec("FLUSH PRIVILEGES");
    }

    // ✅ Midlertidig: gi adminbruker full tilgang til user_details
    // ❗ Husk å fjerne dette etter én gangs kjøring
    try {
        $pdo->exec("FLUSH PRIVILEGES");
    } catch (PDOException $e) {
        // Ikke fatal
    }

    header("Location: ../admin/brukeroversikt.php?oppdatert=1");
    exit();
} catch (PDOException $e) {
    die("❌ Feil ved MySQL-brukerbehandling: " . $e->getMessage());
}
