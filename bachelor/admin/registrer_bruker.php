<?php
require_once("../db2.php");

function validateInput($data, $pattern) {
    return preg_match($pattern, $data);
}

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

$patterns = [
    "fornavn"         => "/^[\p{L}\-]{2,}$/u",
    "etternavn"       => "/^[\p{L}\-]{2,}$/u",
    "User"            => "/^[a-zA-Z0-9_-]{3,16}$/",
    "epost"           => "/^[^@\s]+@[^@\s]+\.[^@\s]+$/",
    "bekreft_epost"   => "/^[^@\s]+@[^@\s]+\.[^@\s]+$/",
    "telefon"         => "/^\d{8}$/",
    "passord"         => "/^.{6,}$/",
    "bekreft_passord" => "/^.{6,}$/"
];

$errors = [];
$data = [];

foreach ($patterns as $key => $pattern) {
    if (!isset($_POST[$key]) || !validateInput($_POST[$key], $pattern)) {
        $errors[] = ucfirst($key) . " har ugyldig eller manglende verdi.";
    } else {
        $data[$key] = sanitize($_POST[$key]);
    }
}

if ($_POST['epost'] !== $_POST['bekreft_epost']) {
    $errors[] = "E-postadressene er ikke like.";
}
if ($_POST['passord'] !== $_POST['bekreft_passord']) {
    $errors[] = "Passordene er ikke like.";
}

if (!empty($errors)) {
    echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
    exit;
}

$User = $data['User'];
$passord = $_POST['passord']; 

try {
    $pdo->beginTransaction();

    $stmtCheck = $pdo->prepare("SELECT `User` FROM mysql.user WHERE `User` = :User AND Host = '%'");
    $stmtCheck->execute([':User' => $User]);
    if ($stmtCheck->fetch()) {
        throw new Exception("Brukernavn '$User' eksisterer allerede.");
    }

    $safePassword = $pdo->quote($passord);
    $pdo->exec("CREATE USER '$User'@'%' IDENTIFIED BY $safePassword");

    $pdo->exec("GRANT USAGE ON *.* TO '$User'@'%'");

    if (!empty($_POST['adminrettigheter'])) {
        $pdo->exec("GRANT `adminbruker` TO '$User'@'%'");
    }

    $pdo->exec("FLUSH PRIVILEGES");

    $stmtInsert = $pdo->prepare("
        INSERT INTO user_details (User, fornavn, etternavn, telefon, epost)
        VALUES (:User, :fornavn, :etternavn, :telefon, :epost)
    ");
    $stmtInsert->execute([
        ':User'     => $User,
        ':fornavn'  => $data['fornavn'],
        ':etternavn'=> $data['etternavn'],
        ':telefon'  => $data['telefon'],
        ':epost'    => $data['epost']
    ]);

    $pdo->commit();

    header("Location: ../admin/brukeroversikt.php");
    exit();


} catch (Exception $e) {
    $pdo->rollBack();
    echo "<script>alert('❌ Feil: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    exit;
}
?>
