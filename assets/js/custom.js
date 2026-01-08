/* ==================================================
   HELPER CHUNG
================================================== */
function qs(selector) {
  return document.querySelector(selector);
}

function qsa(selector) {
  return document.querySelectorAll(selector);
}

/* ==================================================
   VALIDATE FORM ĐĂNG KÝ (signup.php)
================================================== */
function validateRegisterForm() {
  const username = qs("[name='txtTendangnhap']")?.value.trim();
  const pass1 = $("[name='txtMatkhau']")?.value;
  const pass2 = $("[name='txtreMatkhau']")?.value;
  const email = $("[name='txtEmail']")?.value.trim();

  if (!username || !pass1 || !pass2 || !email) return false;

  const usernameRegex = /^[a-zA-Z0-9_]{4,20}$/;
  if (!usernameRegex.test(username)) {
    alert(
      "Tên đăng nhập chỉ được chứa chữ, số, dấu gạch dưới và dài 4-20 ký tự."
    );
    return false;
  }

  if (pass1.length < 6) {
    alert("Mật khẩu phải ít nhất 6 ký tự!");
    return false;
  }

  if (pass1 !== pass2) {
    alert("Mật khẩu nhập lại không khớp!");
    return false;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Email không hợp lệ!");
    return false;
  }

  return true;
}

/* ==================================================
   SHOW / HIDE PASSWORD (login.php, signup.php)
================================================== */
function togglePassword(inputId, icon) {
  const input = document.getElementById(inputId);
  if (!input) return;

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

/* ==================================================
   HIỂN THỊ TÊN FILE UPLOAD
================================================== */
function updateFileName(input) {
  const fileNameSpan = document.getElementById("fileName");
  if (!fileNameSpan) return;

  if (input.files && input.files[0]) {
    fileNameSpan.innerText = input.files[0].name;
    fileNameSpan.classList.add("text-primary");
    fileNameSpan.classList.remove("text-muted");
  } else {
    fileNameSpan.innerText = "Chưa chọn ảnh nào";
    fileNameSpan.classList.remove("text-primary");
    fileNameSpan.classList.add("text-muted");
  }
}

/* ==================================================
   MEGA MENU – XEM THÊM / THU GỌN
================================================== */
$(document).ready(function () {
  $(".btn-toggle-sub").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $btn = $(this);
    const $parentUl = $btn.closest(".sub-list");
    const $extraItems = $parentUl.find(".extra-sub");

    if ($btn.attr("data-state") === "collapsed") {
      $extraItems
        .removeClass("d-none")
        .addClass("animate__animated animate__fadeIn");
      $btn.html("Thu gọn");
      $btn.attr("data-state", "expanded");
    } else {
      $extraItems.addClass("d-none");
      $btn.html("Xem thêm...");
      $btn.attr("data-state", "collapsed");
    }
  });
});

/* ==================================================
   LƯU / BỎ LƯU TÀI LIỆU (document_detail.php)
================================================== */
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btn-save");
  if (!btn) return;

  e.preventDefault();

  const docId = btn.getAttribute("data-id");
  if (!docId) return;

  fetch("toggle_save_document.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "document_id=" + docId,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "login") {
        alert("Vui lòng đăng nhập để lưu tài liệu.");
        window.location.href = "login.php";
        return;
      }

      updateSaveUI(docId, data.status === "saved");
    })
    .catch(console.error);
});

function updateSaveUI(docId, isSaved) {
  // ===== CẬP NHẬT NÚT TRONG LIST =====
  document
    .querySelectorAll('.btn-save[data-id="' + docId + '"]')
    .forEach((btn) => {
      if (btn.id === "modalSave") return;

      const icon = btn.querySelector("i");
      if (!icon) return;

      icon.classList.toggle("fas", isSaved);
      icon.classList.toggle("far", !isSaved);
    });

  // ===== CẬP NHẬT NÚT TRONG MODAL =====
  const modalSave = document.getElementById("modalSave");
  if (!modalSave || modalSave.dataset.id != docId) return;

  const icon = modalSave.querySelector("i");
  const text = modalSave.querySelector(".save-text");

  if (!icon || !text) return;

  if (isSaved) {
    icon.classList.remove("far");
    icon.classList.add("fas");
    text.textContent = "Đã lưu";
  } else {
    icon.classList.remove("fas");
    icon.classList.add("far");
    text.textContent = "Lưu";
  }
}

/* ==================================================
   PROFILE – HIỂN THỊ FORM ĐỔI MẬT KHẨU
================================================== */
function togglePasswordForm() {
  const wrapper = document.getElementById("profileWrapper");
  if (!wrapper) return;

  wrapper.classList.toggle("show-password");
  wrapper.classList.toggle("justify-center");
}

/* ==================================================

/* =================================================
   TÀI LIỆU NỔI BẬT – SLIDER
================================================== */
let featuredIndex = 0;

function slideFeatured(direction) {
  const track = document.getElementById("featuredTrack");
  const items = track.children;
  if (items.length === 0) return;

  const gap = 16;
  const itemWidth = items[0].offsetWidth + gap;
  const wrapperWidth = track.parentElement.offsetWidth;

  // Tính số item hiển thị thực tế
  const visibleItems = Math.floor(wrapperWidth / itemWidth);
  const maxIndex = items.length - visibleItems;

  featuredIndex += direction;

  // Logic Vòng lặp (Loop)
  if (featuredIndex < 0) {
    featuredIndex = maxIndex; 
  } else if (featuredIndex > maxIndex) {
    featuredIndex = 0; 
  }

  track.style.transform = `translateX(-${featuredIndex * itemWidth}px)`;
}

/* ==================================================
                    SHARE DOCUMENT
================================================== */
function copyShareLink() {
  const input = document.getElementById("shareLink");
  input.select();
  input.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(input.value);

  alert("Đã sao chép liên kết!");
}

/* ==================================================
                    MENU DOT
================================================== */
document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.getElementById("docOverlay");
  const modal = document.getElementById("docModal");
  const closeBtn = document.querySelector(".close-modal");

  const modalView = document.getElementById("modalView");
  const modalDownload = document.getElementById("modalDownload");
  const modalSave = document.getElementById("modalSave");

  document.querySelectorAll(".page-more").forEach((el) => {
    el.addEventListener("click", () => {
      const id = el.dataset.id;

      document.getElementById("modalTitle").innerText = el.dataset.title;
      document.getElementById("modalThumb").src = el.dataset.thumb;
      document.getElementById("modalDesc").innerText = el.dataset.desc;
      document.getElementById("modalViewCount").innerText = el.dataset.views;
      document.getElementById("modalDownloadCount").innerText =
        el.dataset.downloads;
      document.getElementById("modalPageCount").innerText = el.dataset.pages;

      modalView.href = "document_detail.php?id=" + id;
      modalDownload.href = "download.php?id=" + id;

      const cardSaveBtn = document.querySelector(
        `.btn-save[data-id="${id}"] i`
      );
      const isSaved = cardSaveBtn?.classList.contains("fas");

      modalSave.dataset.id = id;
      updateSaveUI(id, isSaved);

      overlay.style.display = "block";
      modal.classList.add("show");
    });
  });

  function closeModal() {
    overlay.style.display = "none";
    modal.classList.remove("show");
  }

  overlay.addEventListener("click", closeModal);
  closeBtn.addEventListener("click", closeModal);
});
