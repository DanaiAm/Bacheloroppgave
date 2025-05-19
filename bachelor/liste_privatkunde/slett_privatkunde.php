<?php
// Kobler til databasen
require_once("../db.php");

// Sjekker at forespørselen er en POST og at ID er sendt med
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];

    try {
        // Hent filstien til bildet før sletting
        $stmt = $pdo->prepare("SELECT bilde FROM privatkunde WHERE id = :id");
        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row["bilde"])) {
            $bildeFullPath = realpath(__DIR__ . "/../privatkundebilder/" . basename($row["bilde"]));
            if ($bildeFullPath && file_exists($bildeFullPath)) {
                unlink($bildeFullPath); // Slett bildet
            }
        }

        // Sletter kunden fra databasen
        $stmt = $pdo->prepare("DELETE FROM privatkunde WHERE id = :id");
        $stmt->execute([":id" => $id]);

        // Videresender etter sletting
        header("Location: privatkunde_liste.php");
        exit;
    } catch (PDOException $e) {
        echo "Feil ved sletting: " . htmlspecialchars($e->getMessage());
    }
}
?>
