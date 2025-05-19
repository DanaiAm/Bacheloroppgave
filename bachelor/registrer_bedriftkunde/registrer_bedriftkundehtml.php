<?php
require_once("../db.php");

$id = $_GET['id'] ?? null;
$bedrift = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM bedriftskunde WHERE id = ?");
    $stmt->execute([$id]);
    $bedrift = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>registrer_bedriftskunde</title>

  <link rel="stylesheet" href="../css/registrer.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <script src="../redirectToPage.js"></script>
</head>

<body>
<div id="header">
  <?php include("../header/header.php"); ?>
</div>

<div class="headline-container">
  <button class="secondaryBTN" onclick="redirectToPage('liste_bedriftkunde/bedriftkunde_liste.php')">
    <span class="material-icons pil">arrow_back</span>
  </button>
  <div class="dropdown">
    <button class="dropdown-btn" id="kundegruppeBtn">
      BEDRIFTSKUNDE <span class="material-symbols-outlined pil">arrow_drop_down</span>
    </button>
    <div class="dropdown-content">
      <a href="#" onclick="redirectToPage('registrer_privatkunde/registrer_privatkundehtml.php')">Privatkunde</a>
      <a href="#" onclick="redirectToPage('registrer_borettslag/registrer_borettslaghtml.php')">Borettslag</a>
    </div>
  </div>
</div>

<form method="POST" enctype="multipart/form-data" id="bedriftForm" action="<?= $id ? "../liste_bedriftkunde/oppdater_bedriftkunde.php?id=$id" : "registrer_bedriftkunde.php" ?>">
  <div class="container">
    <div class="form-container">
      <div class="form-left">
        <div class="form-group"><input name="orgnr" type="text" placeholder="Organisasjonsnummer" pattern="^[0-9]{9}$" required value="<?= htmlspecialchars($bedrift['orgnr'] ?? '') ?>"></div>
        <div class="form-group"><input name="bedriftsnavn" type="text" placeholder="Bedriftsnavn" required value="<?= htmlspecialchars($bedrift['bedriftsnavn'] ?? '') ?>"></div>
        <div class="form-group"><input name="adresse1" type="text" placeholder="Adresse 1" required value="<?= htmlspecialchars($bedrift['adresse1'] ?? '') ?>"></div>
        <div class="form-group"><input name="adresse2" type="text" placeholder="Adresse 2" value="<?= htmlspecialchars($bedrift['adresse2'] ?? '') ?>"></div>
        <div class="form-group"><input class="invisble" type="text"></div>
      </div>

      <div class="form-middel">
        <div class="split-input">
          <div class="form-group half-width"><input name="postnr" type="text" placeholder="PostNr." pattern="^[0-9]{4}$" required value="<?= htmlspecialchars($bedrift['postnr'] ?? '') ?>"></div>
          <div class="form-group half-width"><input name="sted" type="text" placeholder="Sted" required value="<?= htmlspecialchars($bedrift['sted'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><input name="epost" type="email" placeholder="E-post" required value="<?= htmlspecialchars($bedrift['epost'] ?? '') ?>"></div>
        <div class="form-group"><input name="kontaktperson" type="text" placeholder="Kontaktperson" required value="<?= htmlspecialchars($bedrift['kontaktperson'] ?? '') ?>"></div>
        <div class="form-group"><input name="kontaktpersonTlf" type="text" placeholder="Kontaktperson telefonnummer" pattern="^[0-9]{8}$" required value="<?= htmlspecialchars($bedrift['kontaktpersonTlf'] ?? '') ?>"></div>
        <div class="form-group"><input class="invisble" type="text"></div>
      </div>

      <div class="form-right">
        <div class="form-group"><textarea name="kommentar" placeholder="Kommentar"><?= htmlspecialchars($bedrift['kommentar'] ?? '') ?></textarea></div>

        <div class="button-container">
          <input type="file" name="bilde" id="imageUpload" accept="image/*" hidden>
          <button id="bilde" class="fileinput" type="button" onclick="document.getElementById('imageUpload').click();">
            <span class="material-icons pil">image</span> Legg til bilde
          </button>

          <input type="file" name="pdf" id="pdfUpload" accept="application/pdf" hidden>
          <button id="PDF" class="fileinput" type="button" onclick="document.getElementById('pdfUpload').click();">
            <span class="material-icons pil">picture_as_pdf</span> Legg til PDF
          </button>
        </div>

        <div class="button-container">
          <button type="submit" class="primaryBTN"><?= $id ? "Oppdater" : "Registrer" ?></button>
        </div>
      </div>
    </div>

    <!-- Forhåndsvisningseksjon (bilder og PDF) flyttet under -->
    <div class="preview-wrapper" style="margin-top: 20px;">
      <div id="bildePreview" class="preview-container">
        <?php if (!empty($bedrift['bilde'])): ?>
          <img src="<?= htmlspecialchars($bedrift['bilde']) ?>" alt="Profilbilde" style="max-width: 200px;">
        <?php endif; ?>
      </div>

      <div id="pdfPreview" class="preview-container">
        <?php if (!empty($bedrift['pdf'])): ?>
          <a href="<?= htmlspecialchars($bedrift['pdf']) ?>" target="_blank">📄 Se eksisterende PDF</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</form>

<script src="registrer_bedriftkunde.js"></script>
</body>
</html>
