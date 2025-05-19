<?php
require_once("../db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Hent verdier fra skjema
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

    // === Håndter bilde ===
    $bildePath = null;
    if (isset($_FILES["bilde"]) && $_FILES["bilde"]["error"] === UPLOAD_ERR_OK) {
        $uploadDir = realpath(__DIR__ . '/../BILDER/bedriftskundebilder') . DIRECTORY_SEPARATOR;
        $ext = pathinfo($_FILES["bilde"]["name"], PATHINFO_EXTENSION);
        $bildeName = uniqid("bilde_", true) . "." . $ext;
        $fullPath = $uploadDir . $bildeName;

        if (move_uploaded_file($_FILES["bilde"]["tmp_name"], $fullPath)) {
            $bildePath = "../BILDER/bedriftskundebilder/" . $bildeName;
        }
    }

    // === Håndter PDF ===
    $pdfPath = null;
    if (isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] === UPLOAD_ERR_OK) {
        $uploadDir = realpath(__DIR__ . '/../PDF/bedriftskundepdf') . DIRECTORY_SEPARATOR;
        $ext = pathinfo($_FILES["pdf"]["name"], PATHINFO_EXTENSION);
        $pdfName = uniqid("pdf_", true) . "." . $ext;
        $fullPath = $uploadDir . $pdfName;

        if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $fullPath)) {
            $pdfPath = "../PDF/bedriftskundepdf/" . $pdfName;
        }
    }

    // === Sett inn i database ===
    $stmt = $pdo->prepare("INSERT INTO bedriftskunde (
        orgnr, bedriftsnavn, adresse1, adresse2, postnr, sted, epost,
        kontaktperson, kontaktpersonTlf, kommentar, bilde, pdf
    ) VALUES (
        :orgnr, :bedriftsnavn, :adresse1, :adresse2, :postnr, :sted, :epost,
        :kontaktperson, :kontaktpersonTlf, :kommentar, :bilde, :pdf
    )");

    $stmt->execute([
        ':orgnr' => $orgnr,
        ':bedriftsnavn' => $bedriftsnavn,
        ':adresse1' => $adresse1,
        ':adresse2' => $adresse2,
        ':postnr' => $postnr,
        ':sted' => $sted,
        ':epost' => $epost,
        ':kontaktperson' => $kontaktperson,
        ':kontaktpersonTlf' => $kontaktpersonTlf,
        ':kommentar' => $kommentar,
        ':bilde' => $bildePath,
        ':pdf' => $pdfPath
    ]);

    // Ferdig
    header("Location: ../liste_bedriftkunde/bedriftkunde_liste.php");
    exit;
} else {
    echo "Ugyldig forespørsel.";
}
?>
