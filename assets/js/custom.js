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
