<?php
require_once("../db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_GET['id'];

    // Hent gamle bilde- og pdf-stier
    $stmtOld = $pdo->prepare("SELECT bilde, pdf FROM privatkunde WHERE id = ?");
    $stmtOld->execute([$id]);
    $gammel = $stmtOld->fetch(PDO::FETCH_ASSOC);

    $fornavn = $_POST["fornavn"];
    $etternavn = $_POST["etternavn"];
    $epost = $_POST["epost"];
    $telefon = $_POST["telefon"];
    $adresse1 = $_POST["adresse1"];
    $adresse2 = $_POST["adresse2"];
    $postnr = $_POST["postnr"];
    $sted = $_POST["sted"];
    $kommentar = $_POST["kommentar"];

    $sql = "UPDATE privatkunde SET 
        fornavn = ?, etternavn = ?, adresse1 = ?, adresse2 = ?, postnr = ?, sted = ?, telefon = ?, epost = ?, kommentar = ?";
    $params = [$fornavn, $etternavn, $adresse1, $adresse2, $postnr, $sted, $telefon, $epost, $kommentar];

    // === BILDE ===
    if (isset($_FILES["bilde"]) && $_FILES["bilde"]["error"] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES["bilde"]["name"], PATHINFO_EXTENSION);
        $bildeName = uniqid("bilde_", true) . "." . $ext;
        $uploadDir = realpath(__DIR__ . '/../BILDER/privatkundebilder') . DIRECTORY_SEPARATOR;
        $fullPath = $uploadDir . $bildeName;

        if (move_uploaded_file($_FILES["bilde"]["tmp_name"], $fullPath)) {
            // Slett gammel bildefil hvis eksisterer og ny ble lastet opp
            if (!empty($gammel['bilde'])) {
                $gammelFil = realpath(__DIR__ . "/../" . $gammel['bilde']);
                if ($gammelFil && file_exists($gammelFil)) {
                    unlink($gammelFil);
                }
            }

            $bildePath = "BILDER/privatkundebilder/" . $bildeName;
            $sql .= ", bilde = ?";
            $params[] = $bildePath;
        }
    }

    // === PDF ===
    if (isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] === UPLOAD_ERR_OK) {
        $pdfName = uniqid("pdf_", true) . "." . pathinfo($_FILES["pdf"]["name"], PATHINFO_EXTENSION);
        $uploadDir = realpath(__DIR__ . '/../PDF/privatkundepdf') . DIRECTORY_SEPARATOR;
        $pdfFullPath = $uploadDir . $pdfName;

        if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $pdfFullPath)) {
            // Slett gammel PDF hvis eksisterer og ny ble lastet opp
            if (!empty($gammel['pdf'])) {
                $gammelPdf = realpath(__DIR__ . "/../" . $gammel['pdf']);
                if ($gammelPdf && file_exists($gammelPdf)) {
                    unlink($gammelPdf);
                }
            }

            $pdfPath = "PDF/privatkundepdf/" . $pdfName;
            $sql .= ", pdf = ?";
            $params[] = $pdfPath;
        }
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        header("Location: ../liste_privatkunde/privatkunde_liste.php");
        exit();
    } else {
        echo "Feil under oppdatering av privatkunde.";
    }
} else {
    echo "Ugyldig forespørsel.";
}
?>
