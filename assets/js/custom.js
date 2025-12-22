// Validate form đăng ký
function validateRegisterForm() {
  let username = document.querySelector("[name='txtTendangnhap']").value.trim();
  let pass1 = document.querySelector("[name='txtMatkhau']").value;
  let pass2 = document.querySelector("[name='txtreMatkhau']").value;
  let email = document.querySelector("[name='txtEmail']").value.trim();

  // Validate username
  let usernameRegex = /^[a-zA-Z0-9_]{4,20}$/;
  if (!usernameRegex.test(username)) {
    alert(
      "Tên đăng nhập chỉ được chứa chữ, số, dấu gạch dưới và dài 4-20 ký tự."
    );
    return false;
  }

  // Validate password
  if (pass1.length < 6) {
    alert("Mật khẩu phải ít nhất 6 ký tự!");
    return false;
  }

  if (pass1 !== pass2) {
    alert("Mật khẩu nhập lại khôngkhớp!");
    return false;
  }

  // Validate email
  let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Email không hợp lệ!");
    return false;
  }

  return true;
}

//Show hide passwword
function togglePassword(inputId, icon) {
  var input = document.getElementById(inputId);

  if (input.type === "password") {
    // Chuyển sang hiện chữ
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    // Chuyển lại thành ẩn
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

//Hiển thị tên file
function updateFileName(input) {
  var fileNameSpan = document.getElementById("fileName");
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

// Mega Menu - Xem thêm / Thu gọn
$(document).ready(function () {
  // Xử lý logic Xem thêm / Thu gọn
  $(".btn-toggle-sub").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation(); // Ngăn menu bị đóng khi click bên trong

    var $btn = $(this);
    var $parentUl = $btn.closest(".sub-list");
    var $extraItems = $parentUl.find(".extra-sub");

    if ($btn.attr("data-state") === "collapsed") {
      // ĐANG THU GỌN -> MỞ RA
      $extraItems
        .removeClass("d-none")
        .addClass("animate__animated animate__fadeIn");
      $btn.html("Thu gọn");
      $btn.attr("data-state", "expanded");
    } else {
      // ĐANG MỞ -> THU GỌN LẠI
      $extraItems.addClass("d-none");
      $btn.html("Xem thêm...");
      $btn.attr("data-state", "collapsed");
    }
  });
});

// Xử lý lưu tài liệu
document.getElementById("btn-save").addEventListener("click", function () {
  fetch("toggle_save_document.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "document_id=" + this.dataset.id,
  })
    .then((res) => res.text())
    .then((text) => {
      try {
        const data = JSON.parse(text);
        if (data.status === "login") {
          alert("Vui lòng đăng nhập để sử dụng chức năng này.");
        } else if (data.status === "saved") {
          alert("Đã lưu tài liệu");
          location.reload();
        } else if (data.status === "unsaved") {
          alert("Đã bỏ lưu");
          location.reload();
        }
      } catch (e) {
        console.error("JSON error:", e);
        console.log("Server response:", text);
      }
    });
});
