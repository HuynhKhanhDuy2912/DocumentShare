  <?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  require("../config.php");

  // --- Kiểm tra đăng nhập ---
  if (!isset($_SESSION['emailUser'])) {
    header("Location: ../login.php");
    exit();
  }

  // --- Lấy thông tin user từ database ---
  $email = $_SESSION['emailUser'];
  $sql = "SELECT username, role, avatar FROM users WHERE email='$email' LIMIT 1";
  $result = $conn->query($sql);

  if ($result->num_rows == 0) {
    session_destroy();
    header("Location: ../login.php");
    exit();
  }

  $user = $result->fetch_assoc();

  // --- Cập nhật lại Session ---
  $_SESSION['username'] = $user['username'];
  $_SESSION['role'] = $user['role'];
  $_SESSION['avatar'] = $user['avatar'];

  // --- Chặn user thường ---
  if ($_SESSION['role'] != 1) {
    echo "<h2 style='color:red; text-align:center; margin-top:50px;'>Bạn không có quyền truy cập trang Admin</h2>";
    exit();
  }


  // --- Logic tải trang ---
  $page = $_GET['p'] ?? 'dashboard';
  $content_file = "pages/$page.php";

  /* MAP TIÊU ĐỀ TRANG */
  $page_titles = [
    'dashboard'     => 'Trang chủ',
    'users'         => 'Quản lý tài khoản',
    'categories'    => 'Quản lý chủ đề',
    'subcategories' => 'Quản lý môn học',
    'documents'     => 'Quản lý tài liệu',
    'uploads'       => 'Quản lý tài liệu đăng tải',
    'comments'      => 'Quản lý bình luận',
    'slideshows'    => 'Quản lý slideshow',
  ];

  $page_title = $page_titles[$page] ?? ucfirst($page);


  // --- Thông tin user hiển thị ---
  $logged_in_name = $_SESSION['username'];
  $avatar_filename = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : '';

  $avatar_display_path = "dist/img/avatar5.png";

  if (!empty($avatar_filename)) {
    $avatar_display_path = "../uploads/users/" . $avatar_filename;
  }

  ?>

  <?php include("includes/header.php"); ?>

  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

      <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="../assets/img/logo.jpg" height="60" width="60">
      </div>

      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
          <?php if ($page != 'dashboard'): ?>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="index.php" class="nav-link"><i class="fa fa-home"></i> Trang chủ</a>
          </li>          
          <?php endif; ?>
        </ul>

        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
              <i class="fas fa-expand-arrows-alt"></i>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
              <i class="fas fa-th-large"></i>
            </a>
          </li>
        </ul>
      </nav>
      <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="../index.php" class="brand-link text-center">
          <img src="../assets/img/logo.jpg" height="50" width="50" style="border-radius: 50%;">
          <span class="brand-text fw-bold">DocumentShare</span>
        </a>

        <div class="sidebar">
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
              <img src="<?php echo $avatar_display_path; ?>" class="img-circle elevation-2" alt="User Image" style="width: 34px; height: 34px; object-fit: cover;">
            </div>
            <div class="info">
              <a href="#" class="d-block">Xin chào, <?php echo $logged_in_name; ?>!</a>
            </div>
          </div>

          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

              <li class="nav-item">
                <a href="index.php?p=dashboard" class="nav-link <?php if ($page == 'dashboard') echo 'active'; ?>">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="index.php?p=users" class="nav-link <?php if ($page == 'users') echo 'active'; ?>">
                  <i class="nav-icon fas fa-users"></i>
                  <p>Quản lý tài khoản</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?p=categories" class="nav-link <?php if ($page == 'categories') echo 'active'; ?>">
                  <i class="nav-icon fas fa-th"></i>
                  <p>Quản lý chủ đề</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?p=subcategories" class="nav-link <?php if ($page == 'subcategories') echo 'active'; ?>">
                  <i class="nav-icon fas fa-list"></i>
                  <p>Quản lý môn học</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?p=documents" class="nav-link <?php if ($page == 'documents') echo 'active'; ?>">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>Quản lý tài liệu</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?p=uploads" class="nav-link <?php if ($page == 'uploads') echo 'active'; ?>">
                  <i class="nav-icon fas fa-cloud-upload-alt"></i>
                  <p>Quản lý tài liệu đăng tải</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?p=comments" class="nav-link <?php if ($page == 'comments') echo 'active'; ?>">
                  <i class="nav-icon fa fa-comments"></i>
                  <p>Quản lý bình luận</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?p=contacts" class="nav-link <?php if ($page == 'contacts') echo 'active'; ?>">
                  <i class="nav-icon fa fa-envelope"></i>
                  <p>Quản lý liên hệ</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="index.php?p=slideshows" class="nav-link">
                  <i class="nav-icon fas fa-images"></i>
                  <p>Quản lý slideshow</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../logout.php" class="nav-link text-danger">
                  <i class="nav-icon fas fa-sign-out-alt"></i>
                  <p>Đăng xuất</p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </aside>

      <div class="content-wrapper">
        <?php if ($page != 'dashboard'): ?>
        <section class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1><?php echo $page_title; ?></h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                  <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                </ol>
              </div>
            </div>
          </div>
        </section>
        <?php endif; ?>

        <section class="content">
          <div class="container-fluid">
            <?php
            // 3. Logic tải file nội dung vào khung
            if (file_exists($content_file) && strpos($page, '/') === false && strpos($page, '..') === false) {
              // Tải nội dung từ pages/dashboard.php, pages/users.php,...
              include($content_file);
            } else {
              // Hiển thị lỗi 404
              echo '<div class="alert alert-danger"><h1>404</h1><p>Nội dung trang <strong>' . htmlspecialchars($page) . '</strong> không tồn tại.</p></div>';
            }
            ?>
          </div>
        </section>
      </div>
      <?php include("includes/footer.php"); ?>
    </div>
  </body>

  </html>