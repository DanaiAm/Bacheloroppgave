<?php
require_once("../db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_GET['id'];

    // Hent eksisterende filstier fra databasen
    $stmt = $pdo->prepare("SELECT bilde, pdf FROM bedriftskunde WHERE id = ?");
    $stmt->execute([$id]);
    $eksisterende = $stmt->fetch(PDO::FETCH_ASSOC);

    // Hent skjema-data
    $orgnr = $_POST["orgnr"];
    $bedriftsnavn = $_POST["bedriftsnavn"];
    $adresse1 = $_POST["adresse1"];
    $adresse2 = $_POST["adresse2"];
    $postnr = $_POST["postnr"];
    $sted = $_POST["sted"];
    $epost = $_POST["epost"];
    $kontaktperson = $_POST["kontaktperson"];
    $kontaktpersonTlf = $_POST["kontaktpersonTlf"];
    $kommentar = $_POST["kommentar"];

    $bildePath = null;
    $pdfPath = null;

    // === Bildeopplasting ===
    if (isset($_FILES["bilde"]) && $_FILES["bilde"]["error"] === UPLOAD_ERR_OK) {
        if (!empty($eksisterende['bilde'])) {
            $gammeltRelativt = $eksisterende['bilde'];
            $gammeltAbsolutt = realpath(__DIR__ . '/../' . ltrim($gammeltRelativt, './'));
            if ($gammeltAbsolutt && file_exists($gammeltAbsolutt)) {
                unlink($gammeltAbsolutt);
            }
        }

        $uploadDir = realpath(__DIR__ . '/../BILDER/bedriftskundebilder') . DIRECTORY_SEPARATOR;
        $ext = pathinfo($_FILES["bilde"]["name"], PATHINFO_EXTENSION);
        $bildeName = uniqid("bilde_", true) . "." . $ext;
        $fullPath = $uploadDir . $bildeName;

        if (move_uploaded_file($_FILES["bilde"]["tmp_name"], $fullPath)) {
            $bildePath = "../BILDER/bedriftskundebilder/" . $bildeName;
        }
    }

    // === PDF-opplasting ===
    if (isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] === UPLOAD_ERR_OK) {
        if (!empty($eksisterende['pdf'])) {
            $gammelPdfRelativ = $eksisterende['pdf'];
            $gammelPdfAbsolutt = realpath(__DIR__ . '/../' . ltrim($gammelPdfRelativ, './'));
            if ($gammelPdfAbsolutt && file_exists($gammelPdfAbsolutt)) {
                unlink($gammelPdfAbsolutt);
            }
        }

        $pdfUploadDir = realpath(__DIR__ . '/../PDF/bedriftskundepdf') . DIRECTORY_SEPARATOR;
        $pdfName = uniqid() . "_" . basename($_FILES["pdf"]["name"]);
        $pdfFullPath = $pdfUploadDir . $pdfName;

        if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $pdfFullPath)) {
            $pdfPath = "../PDF/bedriftskundepdf/" . $pdfName;
        }
    }

    // === SQL-oppdatering ===
    $sql = "UPDATE bedriftskunde SET 
        orgnr = ?, bedriftsnavn = ?, adresse1 = ?, adresse2 = ?, postnr = ?, sted = ?, 
        epost = ?, kontaktperson = ?, kontaktpersonTlf = ?, kommentar = ?";

    $params = [$orgnr, $bedriftsnavn, $adresse1, $adresse2, $postnr, $sted, $epost, $kontaktperson, $kontaktpersonTlf, $kommentar];

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
        header("Location: ../liste_bedriftkunde/bedriftkunde_liste.php");
        exit();
    } else {
        echo "Feil under oppdatering av bedriftskunde.";
    }
} else {
    echo "Ugyldig forespørsel.";
}
?>
