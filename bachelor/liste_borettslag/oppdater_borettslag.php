<?php
require_once("../db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_GET['id'];

    // Hent eksisterende rader
    $stmt = $pdo->prepare("SELECT bilde, pdf FROM borettslagkunde WHERE id = ?");
    $stmt->execute([$id]);
    $eksisterende = $stmt->fetch(PDO::FETCH_ASSOC);

    // Skjemadata
    $orgnr = $_POST["orgnr"];
    $navn = $_POST["navn"];
    $styreleder = $_POST["styreleder"];
    $adresse1 = $_POST["adresse1"];
    $adresse2 = $_POST["adresse2"];
    $postnr = $_POST["postnr"];
    $sted = $_POST["sted"];
    $epost = $_POST["epost"];
    $telefon = $_POST["telefon"];
    $kontaktperson = $_POST["kontaktperson"];
    $kontaktpersonTlf = $_POST["kontaktpersonTlf"];
    $kommentar = $_POST["kommentar"];

    $bildePath = null;
    $pdfPath = null;

    // === Bilde ===
    if (isset($_FILES["bilde"]) && $_FILES["bilde"]["error"] === UPLOAD_ERR_OK) {
        if (!empty($eksisterende['bilde'])) {
            $gammeltBilde = realpath(__DIR__ . '/../' . ltrim($eksisterende['bilde'], './'));
            if (file_exists($gammeltBilde)) unlink($gammeltBilde);
        }

        $uploadDir = realpath(__DIR__ . '/../BILDER/borettslagbilder');
        $ext = pathinfo($_FILES["bilde"]["name"], PATHINFO_EXTENSION);
        $newName = uniqid("bilde_", true) . "." . $ext;
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

        if (move_uploaded_file($_FILES["bilde"]["tmp_name"], $fullPath)) {
            $bildePath = "../BILDER/borettslagbilder/" . $newName;
        }
    }

    // === PDF ===
    if (isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] === UPLOAD_ERR_OK) {
        if (!empty($eksisterende['pdf'])) {
            $gammelPdf = realpath(__DIR__ . '/../' . ltrim($eksisterende['pdf'], './'));
            if (file_exists($gammelPdf)) unlink($gammelPdf);
        }

        $uploadDir = realpath(__DIR__ . '/../PDF/borettslagpdf');
        $pdfName = uniqid("pdf_", true) . "_" . basename($_FILES["pdf"]["name"]);
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $pdfName;

        if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $fullPath)) {
            $pdfPath = "../PDF/borettslagpdf/" . $pdfName;
        }
    }

    // Oppdater spørring
    $sql = "UPDATE borettslagkunde SET 
        orgnr = ?, navn = ?, styreleder = ?, adresse1 = ?, adresse2 = ?, 
        postnr = ?, sted = ?, epost = ?, telefon = ?, kontaktperson = ?, kontaktpersonTlf = ?, kommentar = ?";

    $params = [$orgnr, $navn, $styreleder, $adresse1, $adresse2, $postnr, $sted, $epost, $telefon, $kontaktperson, $kontaktpersonTlf, $kommentar];

    if ($bildePath !== null) {
        $sql .= ", bilde = ?";
        $params[] = $bildePath;
    }

    if ($pdfPath !== null) {
        $sql .= ", pdf = ?";
        $params[] = $pdfPath;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        header("Location: ../liste_borettslag/borettslag_liste.php");
        exit();
    } else {
        echo "Feil under oppdatering av borettslag.";
    }
} else {
    echo "Ugyldig forespørsel.";
}
?>
