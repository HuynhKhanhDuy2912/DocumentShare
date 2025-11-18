// Validate form đăng ký
function validateRegisterForm() {

    let username = document.querySelector("[name='txtTendangnhap']").value.trim();
    let pass1 = document.querySelector("[name='txtMatkhau']").value;
    let pass2 = document.querySelector("[name='txtreMatkhau']").value;
    let email = document.querySelector("[name='txtEmail']").value.trim();

    // Validate username
    let usernameRegex = /^[a-zA-Z0-9_]{4,20}$/;
    if (!usernameRegex.test(username)) {
        alert("Tên đăng nhập chỉ được chứa chữ, số, dấu gạch dưới và dài 4-20 ký tự.");
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

