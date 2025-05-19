<?php
// Inkluderer databaseoppsett
require_once("../db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];

    try {
        // Hent bildebanen for borettslaget
        $stmt = $pdo->prepare("SELECT bilde FROM borettslagkunde WHERE id = :id");
        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row["bilde"])) {
            $bildePath = realpath(__DIR__ . "/../BILDER/borettslagbilder/" . basename($row["bilde"]));
            if ($bildePath && file_exists($bildePath)) {
                unlink($bildePath); // Slett bilde fra filsystemet
            }
        }

        // Slett borettslagkunden fra databasen
        $stmt = $pdo->prepare("DELETE FROM borettslagkunde WHERE id = :id");
        $stmt->execute([":id" => $id]);

        header("Location: borettslag_liste.php");
        exit;
    } catch (PDOException $e) {
        echo "Feil ved sletting: " . htmlspecialchars($e->getMessage());
    }
}
?>
