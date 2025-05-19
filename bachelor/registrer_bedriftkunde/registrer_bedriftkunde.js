document.addEventListener("DOMContentLoaded", () => {
  const pdfInput = document.getElementById("pdfUpload");
  const pdfPreview = document.getElementById("pdfPreview");

  pdfInput.addEventListener("change", function () {
    const file = this.files[0];
    if (file && file.type === "application/pdf") {
      const url = URL.createObjectURL(file);
      pdfPreview.innerHTML = "";
      const link = document.createElement("a");
      link.href = url;
      link.target = "_blank";
      link.textContent = "📄 Forhåndsvis PDF";
      pdfPreview.appendChild(link);
    } else {
      pdfPreview.innerHTML = "";
    }
  });

  // Bilde forhåndsvisning
  const imageInput = document.getElementById("imageUpload");
  const bildePreview = document.getElementById("bildePreview");

  imageInput.addEventListener("change", function () {
    const file = this.files[0];
    if (file && file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = function (e) {
        bildePreview.innerHTML = "";
        const img = document.createElement("img");
        img.src = e.target.result;
        img.alt = "Forhåndsvisning";
        img.style.maxWidth = "100px";
        bildePreview.appendChild(img);
      };
      reader.readAsDataURL(file);
    } else {
      bildePreview.innerHTML = "";
    }
  });
});
