<?php
include("../db2.php");
session_start();

$erRedigering = false;
$innloggetBruker = $_SESSION['db_username'] ?? '';

// Hent verdier fra GET etter redirect ved feil
$brukerdata = [
    'user'      => $_GET['brukernavn'] ?? '',
    'epost'     => $_GET['epost'] ?? '',
    'fornavn'   => $_GET['fornavn'] ?? '',
    'etternavn' => $_GET['etternavn'] ?? '',
    'telefon'   => $_GET['telefon'] ?? '',
    'adminrettigheter' => $_GET['adminrettigheter'] ?? ''
];

// 🔄 Hent eksisterende brukerdata hvis vi er i redigeringsmodus
if (isset($_GET['brukernavn']) && !isset($_GET['feil'])) {
    $erRedigering = true;
    $brukernavn = $_GET['brukernavn'];

    $stmt = $pdo->prepare("SELECT * FROM user_details WHERE user = ?");
    $stmt->execute([$brukernavn]);
    $data = array_change_key_case($stmt->fetch(PDO::FETCH_ASSOC), CASE_LOWER);

    if ($data) {
        $brukerdata = array_merge($brukerdata, $data);

        // 🔐 Sjekk om brukeren har adminrollen
        $rolleStmt = $pdo->prepare("SELECT 1 FROM mysql.role_edges WHERE to_user = ? AND from_user = 'adminbruker'");
        $rolleStmt->execute([$brukernavn]);
        if ($rolleStmt->fetch()) {
            $brukerdata['adminrettigheter'] = 1;
        }
    } else {
        die("❌ Fant ikke bruker med brukernavn '$brukernavn'.");
    }
}

$feilmelding = $_GET['feil'] ?? '';
$profilbildePath = "/Bacheloroppgave/bachelor/BILDER/profilbilder/" . htmlspecialchars($brukerdata['user']) . ".png";
$profilbildePathWithCacheBuster = $profilbildePath . '?v=' . time();
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>nybruker_admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/registrer.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="../redirectToPage.js"></script>
</head>
<body>

<?php if ($feilmelding): ?>
<script>alert("<?php echo htmlspecialchars($feilmelding); ?>");</script>
<?php endif; ?>

<div id="header"><?php include("../header/header.php"); ?></div>

<div class="headline-container">
    <button class="secondaryBTN" onclick="redirectToPage('landingpage/landingpage.php')">
        <span class="material-icons pil">arrow_back</span>
    </button>
    <h1 class="text-3xl font-light"><?php echo $erRedigering ? "OPPDATER BRUKER" : "NY BRUKER"; ?></h1>
</div>

<form action="<?php echo $erRedigering ? 'rediger_bruker_backend.php' : 'registrer_bruker_backend.php'; ?>"
      method="POST" enctype="multipart/form-data" id="bedriftForm">

    <?php if ($erRedigering): ?>
        <input type="hidden" name="gammelt_brukernavn" value="<?php echo htmlspecialchars($brukerdata['user']); ?>">
    <?php endif; ?>

    <input type="file" id="imageUpload" name="profilbilde" accept="image/*" hidden>

    <div class="container">
        <div class="form-container">
            <!-- Venstre kolonne -->
            <div class="form-group-admin">
                <div class="form-group">
                    <input type="text" name="brukernavn" value="<?php echo htmlspecialchars($brukerdata['user']); ?>" placeholder="Fyll inn brukernavn" required>
                </div>
                <div class="form-group">
                    <input type="text" name="fornavn" value="<?php echo htmlspecialchars($brukerdata['fornavn']); ?>" placeholder="Fornavn" required>
                </div>
                <div class="form-group">
                    <input type="text" name="etternavn" value="<?php echo htmlspecialchars($brukerdata['etternavn']); ?>" placeholder="Etternavn" required>
                </div>
                <div class="form-group">
                    <input type="text" name="telefon" value="<?php echo htmlspecialchars($brukerdata['telefon']); ?>" placeholder="Telefonnummer" pattern="^[0-9]{8}$" required>
                </div>
                <div class="form-group">
                    <input type="email" name="epost" value="<?php echo htmlspecialchars($brukerdata['epost']); ?>" placeholder="E-post" required>
                </div>
            </div>

            <!-- Høyre kolonne -->
            <div class="form-group-admin">
                <div class="form-group">
                    <input type="email" name="bekreft_epost" value="<?php echo htmlspecialchars($brukerdata['epost']); ?>" placeholder="Bekreft E-post" required>
                </div>

                <?php if (!$erRedigering): ?>
                <div class="form-group">
                    <input type="password" name="passord" placeholder="Passord minst 6 tegn" required>
                </div>
                <div class="form-group">
                    <input type="password" name="bekreft_passord" placeholder="Bekreft passord" required>
                </div>
                <?php endif; ?>

                <!-- Adminrettigheter -->
                <div class="checkbox-admin">
                    <h2>Adminrettigheter</h2>
                    <?php if ($brukerdata['user'] === $innloggetBruker): ?>
                        <input type="checkbox" name="adminrettigheter" value="1" checked disabled>
                        <input type="hidden" name="adminrettigheter" value="1">
                        <p style="color: gray; font-size: 0.9rem;">Du kan ikke fjerne dine egne adminrettigheter.</p>
                    <?php else: ?>
                        <input type="checkbox" name="adminrettigheter" value="1" <?php echo !empty($brukerdata['adminrettigheter']) ? 'checked' : ''; ?>>
                    <?php endif; ?>
                </div>

                <div class="button-container">
                    <button class="fileinput" type="reset">
                        <span class="material-icons pil">undo</span> Nullstill
                    </button>
                    <button class="fileinput" id="profilbilde" type="button"
                            onclick="document.getElementById('imageUpload').click();">
                        <span class="material-icons pil">account_circle</span> Profilbilde
                    </button>
                    <button class="primaryBTN" type="submit">
                        <?php echo $erRedigering ? 'Lagre endringer' : 'Registrer'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forhåndsvisning nederst til venstre -->
    <div class="bilde-container">
        <img id="forhåndsvisning"
             src="<?php echo ($erRedigering && file_exists($_SERVER['DOCUMENT_ROOT'] . $profilbildePath)) ? $profilbildePathWithCacheBuster : ''; ?>"
             alt="Forhåndsvisning"
             class="profilbilde-visning"
             style="display: <?php echo ($erRedigering && file_exists($_SERVER['DOCUMENT_ROOT'] . $profilbildePath)) ? 'block' : 'none'; ?>">
    </div>

</form>

<script>
document.getElementById('imageUpload').addEventListener('change', function(event) {
    const preview = document.getElementById('forhåndsvisning');
    const file = event.target.files[0];

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
