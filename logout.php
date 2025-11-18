<?php require("header.php"); ?>
<?php
session_destroy(); // xóa tất cả các session
unset($_SESSION['username']);


echo "<script language=javascript>
	alert ('Bạn đã đăng xuất khỏi tài khoản');
	window.location='./';
	</script>
	";
?>
<?php require("footer.php");?>