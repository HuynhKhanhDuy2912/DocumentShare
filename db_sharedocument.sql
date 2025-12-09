-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 09, 2025 lúc 09:16 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `db_sharedocument`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `status`) VALUES
(1, 'Công Nghệ Thông Tin', '“Tổng hợp tài liệu, giáo trình và khóa học liên quan đến lập trình, mạng máy tính, cơ sở dữ liệu, bảo mật và các lĩnh vực trong ngành Công Nghệ Thông Tin.”', 0),
(2, 'Tài Chính - Ngân Hàng', '“Tổng hợp tài liệu, giáo trình và khóa học liên quan đến tài chính, ngân hàng, kế toán, đầu tư, quản trị rủi ro và các lĩnh vực chuyên môn trong ngành Tài Chính – Ngân Hàng.”', 0),
(3, 'Tiếng Anh - Ngoại Ngữ', '“Tổng hợp tài liệu, giáo trình và khóa học về tiếng Anh và các ngoại ngữ khác, bao gồm ngữ pháp, từ vựng, kỹ năng nghe – nói – đọc – viết và tài liệu luyện thi chứng chỉ quốc tế.”', 0),
(4, 'Kỹ Thuật - Công Nghệ', '“Tổng hợp tài liệu, giáo trình và khóa học về kỹ thuật và công nghệ, bao gồm cơ khí, điện – điện tử, tự động hóa, kỹ thuật công trình và các lĩnh vực ứng dụng khoa học kỹ thuật.”', 0),
(5, 'Khoa Học - Xã Hội', '“Tổng hợp tài liệu, giáo trình và khóa học liên quan đến khoa học xã hội, bao gồm tâm lý học, xã hội học, triết học, lịch sử, văn hóa và các lĩnh vực nghiên cứu về con người và xã hội.”', 0),
(6, 'Khoa Học - Tự Nhiên', '“Tổng hợp tài liệu, giáo trình và khóa học về khoa học tự nhiên, bao gồm toán học, vật lý, hóa học, sinh học, môi trường và các lĩnh vực nghiên cứu quy luật tự nhiên.”', 0),
(7, 'Y - Dược - Sức Khỏe', '“Tổng hợp tài liệu, giáo trình và khóa học về y học, dược học và chăm sóc sức khỏe, bao gồm kiến thức bệnh học, dược lý, điều dưỡng, dinh dưỡng và các lĩnh vực hỗ trợ y tế.”', 0),
(8, 'Nông - Lâm - Thủy Sản', '“Tổng hợp tài liệu, giáo trình và khóa học về Nông – Lâm – Thủy Sản, bao gồm trồng trọt, chăn nuôi, lâm nghiệp, nuôi trồng thủy sản, bảo vệ thực vật và các lĩnh vực phục vụ sản xuất nông nghiệp.”', 0),
(9, 'Kinh Doanh Marketing', '“Tổng hợp tài liệu, giáo trình và khóa học về Kinh Doanh – Marketing, bao gồm quản trị doanh nghiệp, quản lý bán hàng, nghiên cứu thị trường, quảng cáo, thương mại điện tử và các lĩnh vực liên quan đến phát triển kinh doanh và tiếp thị.”', 0),
(10, 'Kinh Tế - Quản Lý', '“Tổng hợp tài liệu, giáo trình và khóa học về Kinh Tế – Quản Lý, bao gồm quản trị kinh doanh, quản lý nguồn nhân lực, tài chính doanh nghiệp, kinh tế vĩ mô và các lĩnh vực liên quan đến quản lý và phát triển tổ chức.”', 0),
(11, 'Luật - Kinh Tế Luật', '“Tổng hợp tài liệu, giáo trình và khóa học về Luật – Kinh tế luật, bao gồm luật dân sự, luật thương mại, luật doanh nghiệp, quản lý pháp lý và các lĩnh vực liên quan đến pháp luật và kinh tế.”', 0),
(12, 'Văn Hoá - Thể thao - Du Lịch', '“Tổng hợp tài liệu, giáo trình và khóa học về Văn Hóa – Thể Thao – Du Lịch, bao gồm văn hóa học, lịch sử, thể thao, quản lý du lịch, hướng dẫn du lịch và các lĩnh vực liên quan đến phát triển văn hóa và dịch vụ du lịch.”', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `username` int(11) NOT NULL,
  `content` int(11) NOT NULL,
  `document_id` int(10) NOT NULL,
  `status` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `documents`
--

CREATE TABLE `documents` (
  `document_id` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `subcategory_id` int(10) NOT NULL,
  `status` int(10) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `views` int(10) NOT NULL,
  `share_link` varchar(255) NOT NULL,
  `shares` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `document_uploads`
--

CREATE TABLE `document_uploads` (
  `document_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `username` varchar(100) NOT NULL,
  `views` int(11) DEFAULT 0,
  `share_link` varchar(500) DEFAULT NULL,
  `shares` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `slideshows`
--

CREATE TABLE `slideshows` (
  `slideshow_id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `imageurl` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `slideshows`
--

INSERT INTO `slideshows` (`slideshow_id`, `title`, `description`, `imageurl`, `status`) VALUES
(6, 'hehehehehehe', 'hihihihi', '1765251745_banner.jpg', 1),
(7, 'aaaaaaa', 'aaaaaaa', '1765251777_banner2.jpg', 1),
(8, 'bbbbbb', 'bbbbbb', '1765251815_banner1.jpg', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `category_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` int(10) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`username`, `fullname`, `password`, `email`, `avatar`, `role`, `google_id`, `status`) VALUES
('jungdung2004', '', '9e9f7516c369a15cc8d08f5eb6849d42', 'jungdung2004@gmail.com', NULL, 0, '109039166477257996447', 0),
('khachhang1', 'Khách Hàng', 'e10adc3949ba59abbe56e057f20f883e', 'duy2912www@gmail.com', 'avt6.jpg', 0, NULL, 0),
('khanhduy', 'Huỳnh Khánh Duy', 'e10adc3949ba59abbe56e057f20f883e', 'duykhanhwww@gmail.com', 'mangekyou.jpg', 1, NULL, 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `document_id` (`document_id`);

--
-- Chỉ mục cho bảng `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `category_id` (`subcategory_id`,`username`);

--
-- Chỉ mục cho bảng `document_uploads`
--
ALTER TABLE `document_uploads`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Chỉ mục cho bảng `slideshows`
--
ALTER TABLE `slideshows`
  ADD PRIMARY KEY (`slideshow_id`);

--
-- Chỉ mục cho bảng `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `document_uploads`
--
ALTER TABLE `document_uploads`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `slideshows`
--
ALTER TABLE `slideshows`
  MODIFY `slideshow_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `document_uploads`
--
ALTER TABLE `document_uploads`
  ADD CONSTRAINT `document_uploads_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_uploads_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
