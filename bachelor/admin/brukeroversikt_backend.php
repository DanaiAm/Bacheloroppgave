<?php
include("../db2.php");

// Sjekk om brukeren er admin
$username = $_SESSION['db_username'] ?? '';
$isAdmin = false;

// Hent brukerens rettigheter med SHOW GRANTS
try {
    $sql = sprintf("SHOW GRANTS FOR '%s'", $username);
    $stmt = $pdo->query($sql);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $grantString = implode(" ", $row);
        if (stripos($grantString, "`adminbruker`") !== false) {
            $isAdmin = true;
            break;
        }
    }

    if (!$isAdmin) {
        die("❌ Du har ikke tilstrekkelige rettigheter for å se denne siden.");
    }

} catch (PDOException $e) {
    echo "Feil ved henting av brukerrettigheter: " . $e->getMessage();
}

// Funksjon for å hente brukerens rolle
function getUserRole($username, $pdo) {
    try {
        $sql = sprintf("SHOW GRANTS FOR '%s'", $username);
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $grantString = implode(" ", $row);
            if (stripos($grantString, "`adminbruker`") !== false) {
                return 'Admin';
            }
        }
    } catch (PDOException $e) {
        error_log("Feil ved sjekking av brukerrettigheter: " . $e->getMessage());
    }

    return 'Bruker';
}

// Hent brukere fra user_details-tabellen
try {
    $sql = "SELECT User, fornavn, etternavn, telefon, epost FROM user_details";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Feil ved henting av brukerinformasjon: " . $e->getMessage());
}

// Slett bruker hvis ?delete_user er satt
if (isset($_GET['delete_user'])) {
    $User = $_GET['delete_user'];

    try {
        $pdo->beginTransaction();

        // Slett fra user_details
        $sqlDeleteUser = "DELETE FROM user_details WHERE User = :User";
        $stmtDeleteUser = $pdo->prepare($sqlDeleteUser);
        $stmtDeleteUser->bindParam(':User', $User, PDO::PARAM_STR);
        $stmtDeleteUser->execute();

        // DROP USER må bygges som SQL-streng (kan ikke bruke prepare)
        $quotedUser = $pdo->quote($User); // f.eks. 'brukernavn'
        $pdo->exec("DROP USER $quotedUser@'%'");

        $pdo->commit();
        header("Location: brukeroversikt.php?slettet=1");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Feil ved sletting av bruker: " . htmlspecialchars($e->getMessage());
    }
}
?>
