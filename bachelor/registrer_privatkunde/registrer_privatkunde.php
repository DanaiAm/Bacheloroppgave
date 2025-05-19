<?php
require_once("../db.php");

function validateInput($data, $pattern) {
    return preg_match($pattern, $data);
}
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

$patterns = [
    "fornavn" => "/^[\p{L}\-]{2,}$/u",
    "etternavn" => "/^[\p{L}\-]{2,}$/u",
    "epost" => "/^[^@\s]+@[^@\s]+\.[^@\s]+$/",
    "telefon" => "/^\d{8}$/",
    "adresse1" => "/^.{2,}$/",
    "adresse2" => "/^.{0,}$/",
    "postnr" => "/^\d{4}$/",
    "sted" => "/^[\p{L}\s\-]{2,}$/u",
    "kommentar" => "/.*/"
];

$errors = [];
$data = [];

foreach ($patterns as $key => $pattern) {
    if (!isset($_POST[$key]) || !validateInput($_POST[$key], $pattern)) {
        $errors[] = "$key har ugyldig eller manglende verdi.";
    } else {
        $data[$key] = sanitize($_POST[$key]);
    }
}

if (!empty($errors)) {
    echo "<script>alert('" . implode("\n", $errors) . "'); window.location.href='registrer_privatkundehtml.php';</script>";
    exit;
}

// Unik filnavn + lagringsstier
$bildePath = "";
if (isset($_FILES['bilde']) && $_FILES['bilde']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['bilde']['type'], $allowedTypes)) {
        $ext = pathinfo($_FILES['bilde']['name'], PATHINFO_EXTENSION);
        $newName = uniqid("bilde_", true) . '.' . $ext;
        $uploadDir = realpath(__DIR__ . '/../BILDER/privatkundebilder');
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

        if (move_uploaded_file($_FILES['bilde']['tmp_name'], $uploadPath)) {
            $bildePath = "BILDER/privatkundebilder/" . $newName;
        }
    }
}

$pdfPath = "";
if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['pdf']['type'] === 'application/pdf') {
        $ext = pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION);
        $newName = uniqid("pdf_", true) . '.' . $ext;
        $uploadDir = realpath(__DIR__ . '/../PDF/privatkundepdf');
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadPath)) {
            $pdfPath = "PDF/privatkundepdf/" . $newName;
        }
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO privatkunde (
        fornavn, etternavn, epost, telefon, adresse1, adresse2, postnr, sted, kommentar, bilde, pdf
    ) VALUES (
        :fornavn, :etternavn, :epost, :telefon, :adresse1, :adresse2, :postnr, :sted, :kommentar, :bilde, :pdf
    )");

    $stmt->execute([
        ':fornavn' => $data['fornavn'],
        ':etternavn' => $data['etternavn'],
        ':epost' => $data['epost'],
        ':telefon' => $data['telefon'],
        ':adresse1' => $data['adresse1'],
        ':adresse2' => $data['adresse2'],
        ':postnr' => $data['postnr'],
        ':sted' => $data['sted'],
        ':kommentar' => $data['kommentar'],
        ':bilde' => $bildePath,
        ':pdf' => $pdfPath
    ]);

    header("Location: ../liste_privatkunde/privatkunde_liste.php");
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Databasefeil: " . $e->getMessage()]);
}
?>
