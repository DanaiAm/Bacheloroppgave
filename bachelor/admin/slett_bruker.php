<?php
include("../db2.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (isset($_GET['User'])) {
    $User = $_GET['User'];
    $innloggetBruker = $_SESSION['db_username'] ?? '';

    // Hindre at man sletter seg selv
    if ($User === $innloggetBruker) {
        echo "<script>alert('❌ Du kan ikke slette din egen bruker.'); window.location.href='../admin/brukeroversikt.php';</script>";
        exit();
    }

    try {
        // Slett fra user_details (i transaksjon)
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM user_details WHERE User = :User");
        $stmt->bindParam(':User', $User);
        $stmt->execute();
        $pdo->commit();

        // REVOKE og DROP USER (utenfor transaksjon)
        try {
            $pdo->exec("REVOKE 'adminbruker' FROM '$User'@'%'");
        } catch (PDOException $e) {}

        try {
            $pdo->exec("DROP USER IF EXISTS '$User'@'%'");
        } catch (PDOException $e) {}

        // Slett profilbildet hvis det finnes
        $bildePath = realpath(__DIR__ . '/../BILDER/profilbilder/' . $User . '.png');
        if ($bildePath && file_exists($bildePath)) {
            unlink($bildePath);
        }

        header("Location: ../admin/brukeroversikt.php?slettet=1");
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>alert('❌ Feil ved sletting: " . htmlspecialchars($e->getMessage()) . "'); window.location.href='../admin/brukeroversikt.php';</script>";
        exit();
    }
} else {
    header("Location: ../admin/brukeroversikt.php");
    exit();
}
