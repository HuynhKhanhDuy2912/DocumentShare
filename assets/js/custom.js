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
document.addEventListener("DOMContentLoaded", function () {
  const btnSave = document.getElementById("btn-save");

  if (!btnSave) return; 

  btnSave.addEventListener("click", function () {
    const docId = this.dataset.id;
    if (!docId) return;

    fetch("toggle_save_document.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "document_id=" + docId,
    })
      .then((res) => {
        if (!res.ok) throw new Error("Network response was not ok");
        return res.text();
      })
      .then((text) => {
        try {
          const data = JSON.parse(text);

          if (data.status === "login") {
            alert("Vui lòng đăng nhập để sử dụng chức năng này.");
            window.location.href = "login.php";
          } else if (data.status === "saved") {
            alert("Đã lưu tài liệu thành công!");
            location.reload();
          } else if (data.status === "unsaved") {
            alert("Đã bỏ lưu tài liệu!");
            location.reload();
          }
        } catch (e) {
          console.error("Lỗi parse JSON:", text);
          alert("Có lỗi xảy ra từ phía server.");
        }
      })
      .catch((err) => console.error("Fetch error:", err));
  });
});

/* ==================================================
   PROFILE – HIỂN THỊ FORM ĐỔI MẬT KHẨU
================================================== */
function togglePasswordForm() {
    const wrapper = document.getElementById("profileWrapper");
    if (!wrapper) return;

    wrapper.classList.toggle("show-password");
    wrapper.classList.toggle("justify-center");
}

