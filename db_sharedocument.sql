-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 30, 2025 lúc 03:33 AM
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
(8, 'Nông - Lâm - Thủy Sản', '“Tổng hợp tài liệu, giáo trình và khóa học về Nông – Lâm – Thủy Sản, bao gồm trồng trọt, chăn nuôi, lâm nghiệp, nuôi trồng thủy sản, bảo vệ thực vật và các lĩnh vực phục vụ sản xuất nông nghiệp.”', 0);

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
  `thumbnail` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(255) NOT NULL,
  `subcategory_id` int(10) NOT NULL,
  `status` int(10) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `views` int(10) NOT NULL,
  `share_link` varchar(255) NOT NULL,
  `downloads` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `documents`
--

INSERT INTO `documents` (`document_id`, `title`, `description`, `thumbnail`, `file_path`, `file_type`, `subcategory_id`, `status`, `username`, `views`, `share_link`, `downloads`) VALUES
(8, 'Bài giảng Hệ quản trị và an toàn cơ sở dữ liệu: Chương 4 - Hệ quản trị cơ sở dữ liệu phi quan hệ', 'Bài giảng Hệ quản trị và an toàn cơ sở dữ liệu: Chương 4 giới thiệu về hệ quản trị CSDL phi quan hệ NoSQL, MongoDB, các kiến thức về khái niệm, ưu nhược điểm và cách sử dụng MongoDB.', '1766460687_thumb_IT1.jpg', '1766460687_BaoCaoDoAnCN.pdf', 'pdf', 7, 0, 'admin', 69, '1766417512_LapTrinhHTML.pdf', 2),
(9, 'Bài giảng An toàn thông tin trong cơ sở dữ liệu: Chương 5 - Phát hiện xâm nhập trái phép', 'Bài giảng An toàn thông tin trong cơ sở dữ liệu: Chương 5 về phát hiện xâm nhập trái phép vào CSDL; tìm hiểu các loại tấn công (bí mật, toàn vẹn), mô hình đe dọa (quản trị, nhân viên, hacker) và giải pháp bảo mật.', '1766460788_thumb_IT2.jpg', '1766460788_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 8, 0, 'admin', 43, '1766419930_HuynhKhanhDuy_DeCuongChiTiet.pdf', 2),
(10, 'Bài giảng Nhập môn tài chính ngân hàng - Chương 7: Tài chính quốc tế', 'Chương 7 giới thiệu về tài chính quốc tế qua các nội dung: Sự hình thành các tổ chức tài chính quốc tế; Phân loại các tổ chức tài chính quốc tế; Vai trò của các tổ chức tài chính quốc tế; Một số tổ chức tài chính quốc tế.', '1766459909_thumb_C7.jpg', '1766459909_BaoCaoDoAnCN.pdf', 'pdf', 16, 0, 'admin', 0, '1766459909_BaoCaoDoAnCN.pdf', 0),
(11, 'Giáo trình Anh văn chuyên ngành (Ngành: Hướng dẫn du lịch - Trung cấp) - Trường Trung cấp Mỹ thuật - Văn hóa Bình Dương', 'Giáo trình Anh văn chuyên ngành du lịch (trình độ Trung cấp) trang bị kiến thức, kỹ năng giao tiếp tiếng Anh cơ bản trong ngành du lịch: đặt phòng, vé máy bay, hướng dẫn khách.', '1766460892_thumb_El1.jpg', '1766460892_BaoCaoDoAnCN.pdf', 'pdf', 15, 0, 'admin', 0, '1766459977_LapTrinhHTML.pdf', 0),
(12, 'Bài giảng Định dạng văn bản', 'Bài giảng Định dạng văn bản trình bày các nội dung về: Tổng quan, định dạng, bảng biểu, style, trộn thư, thiết lập trang, review và tiếng Việt.', '1766461022_thumb_IT3.jpg', '1766461022_BaoCaoDoAnCN.pdf', 'pdf', 5, 0, 'admin', 1, '1766461022_BaoCaoDoAnCN.pdf', 0),
(13, 'Tài liệu học tập Tin ứng dụng trong kinh doanh', 'Tài liệu \"Tin ứng dụng trong kinh doanh\" hướng dẫn sinh viên ngành TàiQuản trị kinh doanh sử dụng Excel để phân tích dữ liệu. Nội dung gồm Excel cơ bản, cơ sở dữ liệu, và các tính năng nâng cao như Goal Seek, Solver.', '1766461086_thumb_IT4.jpg', '1766461086_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 5, 0, 'admin', 1, '1766461086_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(14, 'Ngân hàng câu hỏi môn Linux và mã nguồn mở', 'Tài liệu về Linux và mã nguồn mở: giới thiệu khái niệm, lịch sử, lợi ích, giấy phép (Apache, BSD, GNU GPL, MIT), và các phần mềm phổ biến (Drupal, GIMP).', '1766461161_thumb_IT5.jpg', '1766461161_BaoCaoDoAnCN.pdf', 'pdf', 5, 0, 'admin', 2, '1766461161_BaoCaoDoAnCN.pdf', 0),
(15, 'Ngân hàng câu hỏi trắc nghiệm Kỹ năng sử dụng công nghệ thông tin (Cơ bản và nâng cao)', 'Ngân hàng câu hỏi trắc nghiệm Kỹ năng sử dụng công nghệ thông tin bao gồm các module từ cơ bản đến nâng cao như: Hiểu biết về CNTT cơ bản; sử dụng máy tính cơ bản; xử lý văn bản cơ bản.', '1766461235_thumb_IT6.jpg', '1766461235_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 5, 0, 'admin', 1, '1766461235_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(16, 'Bài giảng Khoa học máy tính: Quản lý một Oracle Instance', 'Bài giảng hướng dẫn về quản lý Oracle Instance gồm các thao tác sau: tạo và quản lý các file tham số (PFILE, SPFILE), khởi động và tắt một instance, thay đổi các trạng thái khởi động Instance.', '1766461357_thumb_IT7.jpg', '1766461357_BaoCaoDoAnCN.pdf', 'pdf', 6, 0, 'admin', 0, '1766461357_BaoCaoDoAnCN.pdf', 0),
(17, 'Bài giảng Khoa học máy tính: Các thành phần cấu trúc của Oracle', 'Bài giảng trình bày về cấu trúc của Oracle bao gồm: Oracle Server (Instance, Database), file dữ liệu (Data, Redo Log, Control), file tham số, file mật khẩu và Archived Log.', '1766461418_thumb_IT7.jpg', '1766461418_BaoCaoDoAnCN.pdf', 'pdf', 6, 0, 'admin', 0, '1766461418_BaoCaoDoAnCN.pdf', 0),
(18, 'Bài giảng Ngôn ngữ hình thức và Ôtômát: Chương 5 - Ôtômat đẩy xuống', 'Bài giảng Ngôn ngữ hình thức và Ôtômát: Chương 5 giới thiệu ôtômat đẩy xuống (PDA), mô hình tính toán cho ngôn ngữ phi ngữ cảnh.', '1766461489_thumb_IT8.jpg', '1766461489_BaoCaoDoAnCN.pdf', 'pdf', 6, 0, 'admin', 0, '1766461489_BaoCaoDoAnCN.pdf', 0),
(19, 'Bài giảng Lập trình C++', 'Bài giảng Lập trình C++ Tổng quan về lập trình hướng đối tượng (OOP) và C++: khái niệm, cú pháp, tính năng. OOP: đối tượng, lớp, trừu tượng, bao gói, kế thừa, đa hình. C++: mở rộng C, vào/ra, cấp phát bộ nhớ, hàm, tải bội, lớp, toán tử, Stream.', '1766461550_thumb_IT9.jpg', '1766461550_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 6, 0, 'admin', 0, '1766461550_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(20, 'Bài giảng Cơ sở dữ liệu: Chương 7 - Truy vấn dữ liệu', 'Bài giảng Cơ sở dữ liệu: Chương 7 tổng quan về SQL gồm: ngôn ngữ truy vấn cấu trúc, môi trường SQL (Catalog, Schema, DDL, DML, DCL), kiểu dữ liệu, câu lệnh SELECT FROM, GROUP BY, HAVING.', '1766461626_thumb_IT10.jpg', '1766461626_BaoCaoDoAnCN.pdf', 'pdf', 7, 0, 'admin', 2, '1766461626_BaoCaoDoAnCN.pdf', 0),
(21, 'Bộ câu hỏi ôn tập môn Khai phá dữ liệu (Data Mining)', 'Tài liệu ôn tập Data Mining trình bày nội dung chính về: Khái niệm, quy trình KDD, thuật toán Apriori, FP-Growth, tiền xử lý dữ liệu, phân cụm, phân lớp, luật kết hợp, Big Data.', '1766461686_thumb_IT11.jpg', '1766461686_BaoCaoDoAnCN.pdf', 'pdf', 7, 0, 'admin', 0, '1766461686_BaoCaoDoAnCN.pdf', 0),
(22, 'Một thuật toán khai thác tập lợi ích cao liên quan có lợi nhuận âm', 'Bài viết trình bày phương pháp đề xuất nhằm phát hiện và khai thác các tập mục có lợi ích cao trong trường hợp lợi nhuận mang giá trị âm, góp phần mở rộng hướng tiếp cận trong lĩnh vực khai thác dữ liệu.', '1766461745_thumb_IT12.jpg', '1766461745_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 7, 0, 'admin', 2, '1766461745_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(23, 'Bài giảng An toàn và bảo mật thông tin', 'Bài giảng về an toàn và bảo mật thông tin (ATBM TT) trong hệ thống thông tin doanh nghiệp: Khái niệm, quy trình, rủi ro, biện pháp bảo vệ và pháp luật liên quan.', '1766461827_thumb_IT13.jpg', '1766461827_BaoCaoDoAnCN.pdf', 'pdf', 8, 0, 'admin', 1, '1766461827_BaoCaoDoAnCN.pdf', 0),
(24, 'Lecture Cryptography: Cryptography Applications (Part 1) - PhD. Ngoc-Tu Nguyen', 'Lecture Cryptography: Cryptography Applications (Part 1) introduces real-world applications of cryptographic principles in authentication, key negotiation, and secure protocols. Topics include SSL/TLS, IPSec, SSH, and Kerberos. The lecture emphasizes practical implementation in networks and systems. Please refer to the lecture for more details!', '1766461897_thumb_IT16.jpg', '1766461897_BaoCaoDoAnCN.pdf', 'pdf', 8, 0, 'admin', 0, '1766461897_BaoCaoDoAnCN.pdf', 0),
(25, 'Giáo trình An toàn mạng (Nghề: Quản trị mạng máy tính - Trình độ: Cao đẳng) - Trường Cao đẳng Thủ Thiêm', 'Giáo trình An toàn mạng cung cấp kiến thức cơ bản về bảo mật mạng, mã hóa thông tin, NAT, tường lửa, ACL, và phòng chống virus. Tài liệu dành cho sinh viên Quản trị Mạng.', '1766461960_thumb_IT7.jpg', '1766461960_BaoCaoDoAnCN.pdf', 'pdf', 8, 0, 'admin', 2, '1766461960_BaoCaoDoAnCN.pdf', 0),
(26, 'Bài giảng Thiết kế hướng đối tượng: Chương 4 - Thiết kế hệ thống', 'Bài giảng Thiết kế hướng đối tượng: Chương 4 trình bày thiết kế hệ thống, biểu đồ lớp, kiến trúc (thành phần, triển khai) và phát sinh mã.', '1766462089_thumb_IT19.jpg', '1766462089_BaoCaoDoAnCN.pdf', 'pdf', 9, 0, 'admin', 0, '1766462089_BaoCaoDoAnCN.pdf', 0),
(27, 'Bài giảng Lập trình website - ThS. Trần Thịnh Mạnh Đức', 'Bài giảng Lập trình website tổng quan về lập trình website: lịch sử Internet, khái niệm mạng (server, client, giao thức, địa chỉ IP), World Wide Web và trang web.', '1766462150_thumb_IT20.jpg', '1766462150_BaoCaoDoAnCN.pdf', 'pdf', 9, 0, 'admin', 1, '1766462150_BaoCaoDoAnCN.pdf', 0),
(28, 'Tài liệu ôn tập môn Lập trình web 1', 'Tài liệu trình bày tổng quan lập trình web: thuật ngữ (website, URL), loại trang (tĩnh, động), hosting, database (MySQL), web application (front-end, back-end).', '1766462222_thumb_IT21.jpg', '1766462222_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 9, 0, 'admin', 2, '1766462222_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(29, 'Bài giảng Thiết kế hướng đối tượng: Chương 2 - Ngôn ngữ mô hình hóa hướng đối tượng', 'Bài giảng Thiết kế hướng đối tượng: Chương 2 giới thiệu ngôn ngữ mô hình hóa hướng đối tượng, giúp mô tả ý tưởng rõ ràng, dễ đọc, trực quan cấu trúc phức tạp và có các khung nhìn khác nhau.', '1766462285_thumb_IT19.jpg', '1766462285_BaoCaoDoAnCN.pdf', 'pdf', 9, 0, 'admin', 0, '1766462285_BaoCaoDoAnCN.pdf', 0),
(30, 'Phát triển ứng dụng phát hiện hư hỏng đường bộ theo thời gian thực', 'Bài viết nghiên cứu phát triển ứng dụng Android phát hiện hư hỏng đường bộ theo thời gian thực, dùng YOLOv12m để đưa ra lựa chọn giải pháp tối ưu cho triển khai thực tế.', '1766462445_thumb_ai1.jpg', '1766462445_BaoCaoDoAnCN.pdf', 'pdf', 10, 0, 'admin', 6, '1766462445_BaoCaoDoAnCN.pdf', 0),
(31, 'Sách bài tập AIO (Kèm bài giải; version 2025)', 'Sách bài tập AIO (2025) cung cấp kiến thức AI từ cơ bản đến nâng cao, toán học, Python, máy học, deep learning và ứng dụng thực tế. Cập nhật kiến thức mới nhất.', '1766462505_thumb_ai2.jpg', '1766462505_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 10, 0, 'admin', 12, '1766462505_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(32, 'Nguyên lý kế toán - Xác định kết quả kinh doanh', 'Tài liệu về nguyên lý kế toán, tập trung vào xác định kết quả kinh doanh: doanh thu, chi phí, lợi nhuận. Hướng dẫn phương pháp kế toán và lập báo cáo.', '1766479511_thumb_H1.jpg', '1766479511_BaoCaoDoAnCN.pdf', 'pdf', 11, 0, 'admin', 0, '1766479511_BaoCaoDoAnCN.pdf', 0),
(33, 'Nguyên lý kế toán - Tiêu thụ sản phẩm', 'Tài liệu này trình bày nguyên lý kế toán tiêu thụ sản phẩm, bao gồm khái niệm, phương pháp hạch toán, tài khoản kế toán, doanh thu, giá vốn, chi phí bán hàng.', '1766479567_thumb_H2.jpg', '1766479567_BaoCaoDoAnCN.pdf', 'pdf', 11, 0, 'admin', 2, '1766479567_BaoCaoDoAnCN.pdf', 0),
(34, 'Bài tập môn Kế toán tổng hợp', 'Tài liệu này bao gồm các câu hỏi và bài tập về kế toán và thuế, cung cấp các kiến thức trọng tâm về: định khoản, thuế giá trị gia tăng và xác định giá thành sản phẩm.', '1766479698_thumb_H3.jpg', '1766479698_BaoCaoDoAnCN.pdf', 'pdf', 11, 0, 'admin', 0, '1766479698_BaoCaoDoAnCN.pdf', 0),
(35, 'Mối quan hệ giữa qui định kế toán và thuế tại Việt Nam: Phân tích từ góc độ lý thuyết', 'Bài viết nghiên cứu về mối quan hệ giữa kế toán và thuế ở Việt Nam, đánh giá mức độ độc lập và đề xuất giải pháp cải thiện. Phân tích qui định giai đoạn 2013-2019.', '1766479755_thumb_H3.jpg', '1766479755_BaoCaoDoAnCN.pdf', 'pdf', 11, 0, 'admin', 0, '1766479755_BaoCaoDoAnCN.pdf', 0),
(36, 'Bài giảng Phân tích dữ liệu trong kiểm toán: Chương 1 - TS. Hoàng Thị Mai Lan', 'Nội dung bài giảng chương 1 trình bày tổng quan, chuẩn mực, phương pháp, tổ chức, độ tin cậy, bằng chứng, hồ sơ, ứng dụng, công cụ (Excel, Python, R, Power BI, Tableau, ACL Analytics, CaseWare IDEA), thách thức và giải pháp.', '1766479866_thumb_t1.jpg', '1766479866_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 12, 0, 'admin', 0, '1766479866_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(37, 'Hệ thống câu hỏi và bài tập trắc nghiệm thi công chức thuế năm 2014', 'Mời các bạn cùng tham khảo tài liệu \"Hệ thống câu hỏi và bài tập trắc nghiệm thi công chức thuế năm 2014\" dưới đây để có thêm tài liệu học tập và ôn thi, tài liệu cung cấp cho các bạn những câu hỏi bài tập trắc nghiệm có đáp án về thuế giá trị gia tăng, thuế thu nhập doanh nghiệp, thuế thu nhập cá nhân,... Chúc các bạn đạt kết quả cao trong kỳ thi sắp tới.', '1766479920_thumb_t2.jpg', '1766479920_HuynhKhanhDuy_DeCuongChiTiet.pdf', 'pdf', 12, 0, 'admin', 0, '1766479920_HuynhKhanhDuy_DeCuongChiTiet.pdf', 0),
(38, 'Tài liệu ôn thi chứng chỉ Kiểm toán và Kế toán viên - Chuyên đề 3: Thuế và quản lý thuế nâng cao', 'Nội dung chuyên đề 3 bao gồm kiến thức chung về thuế và các sắc thuế chủ yếu trong hệ thống thuế Việt Nam. Nội dung kiến thức của chuyên đề đòi hỏi người dự thi chứng chỉ kiểm toán viên và kế toán viên hành nghề phải hiểu sâu sắc nội dung những sắc thuế chủ yếu trong hệ thống thuế Việt Nam.', '1766479969_thumb_t3.jpg', '1766479969_BaoCaoDoAnCN.pdf', 'pdf', 12, 0, 'admin', 0, '1766479969_BaoCaoDoAnCN.pdf', 0),
(39, 'Bài tập tình huống môn Kiểm toán', 'Tài liệu gồm bài tập tình huống kiểm toán: đánh giá rủi ro, thủ tục kiểm toán, chuẩn mực đạo đức. Các tình huống kiểm toán BCTC khác nhau.', '1766480037_thumb_t4.jpg', '1766480037_BaoCaoDoAnCN.pdf', 'pdf', 12, 0, 'admin', 0, '1766480037_BaoCaoDoAnCN.pdf', 0),
(40, 'Bài giảng Định giá tài sản: Chương 5 - ThS. Nguyễn Minh Hiền', 'Bài giảng Định giá tài sản: Chương 5 về định giá doanh nghiệp, bao gồm các nội dung về: Giá trị doanh nghiệp, phương pháp (tài sản thuần, P/E..), quy trình, phương pháp giá trị tài sản thuần.', '1766480113_thumb_d1.jpg', '1766480113_BaoCaoDoAnCN.pdf', 'pdf', 13, 0, 'admin', 0, '1766480113_BaoCaoDoAnCN.pdf', 0),
(41, 'Bài giảng Tài chính nhà quản trị: Chương 7 - Chi phí sử dụng vốn', 'Bài giảng Tài chính nhà quản trị: Chương 7 trình bày về khái niệm, chi phí vốn bộ phận, chi phí vốn trung bình trọng số, chi phí vốn biên tế & quyết định đầu tư.', '1766480166_thumb_d2.jpg', '1766480166_BaoCaoDoAnCN.pdf', 'pdf', 13, 0, 'admin', 0, '1766480166_BaoCaoDoAnCN.pdf', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `document_likes`
--

CREATE TABLE `document_likes` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `username` varchar(100) NOT NULL DEFAULT 'Admin',
  `views` int(11) DEFAULT 0,
  `share_link` varchar(500) DEFAULT NULL,
  `shares` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `saved_documents`
--

CREATE TABLE `saved_documents` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `document_id` int(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `saved_documents`
--

INSERT INTO `saved_documents` (`id`, `username`, `document_id`, `created_at`) VALUES
(1, 'khachhang1', 9, '2025-12-23 09:36:21'),
(3, 'jungdung2004', 20, '2025-12-23 13:43:41'),
(4, 'khachhang1', 28, '2025-12-23 13:52:23'),
(5, 'khachhang1', 31, '2025-12-23 15:19:45'),
(6, 'khachhang1', 30, '2025-12-30 09:22:56');

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

--
-- Đang đổ dữ liệu cho bảng `subcategories`
--

INSERT INTO `subcategories` (`subcategory_id`, `name`, `status`, `category_id`) VALUES
(5, 'Tin học văn phòng', 0, 1),
(6, 'Khoa học máy tính', 0, 1),
(7, 'Khoa học dữ liệu', 0, 1),
(8, 'An toàn thông tin', 0, 1),
(9, 'Kỹ thuật phần mềm', 0, 1),
(10, 'Trí tuệ nhân tạo', 0, 1),
(11, 'Kế toán', 0, 2),
(12, 'Kiểm toán', 0, 2),
(13, 'Tài chính doanh nghiệp', 0, 2),
(14, 'Tiếng anh thông dụng', 0, 3),
(15, 'Tiếng anh chuyên ngành', 0, 3),
(16, 'Ngân hàng - Tín dụng', 0, 2),
(17, 'Ngôn ngữ Anh', 0, 3),
(18, 'Kỹ năng Tiếng anh', 0, 3),
(19, 'Giáo dục học', 0, 5),
(20, 'Tâm lý học', 0, 5),
(21, 'Xã hội học', 0, 5),
(22, 'Triết - Chính trị học', 0, 5),
(23, 'Toán học', 0, 6),
(24, 'Vật lý', 0, 6),
(25, 'Hóa học', 0, 6),
(26, 'Sinh học', 0, 6),
(27, 'Y khoa', 0, 7),
(28, 'Răng - Hàm - Mặt', 0, 7),
(29, 'Dược học', 0, 7),
(30, 'Điều dưỡng', 0, 7),
(31, 'Khoa học cây trồng', 0, 8),
(32, 'Chăn nuôi Thú y', 0, 8),
(33, 'Nuôi trồng thủy sản', 0, 8),
(34, 'Lâm nghiệp - Quảng lý rừng', 0, 8),
(35, 'Cơ khí - Chế tạo máy', 0, 4),
(36, 'Tự động hóa - Điều khiển', 0, 4),
(37, 'Điện - Điện tử - Viễn thông', 0, 4),
(38, 'Kiến trúc - Xây dựng - Vật liệu', 0, 4),
(39, 'Kỹ thuật nhiệt - Lạnh', 0, 4),
(40, 'Khoa học môi trường', 0, 6),
(41, 'Địa lý địa chất', 0, 6);

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
('admin', 'Admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin@gmail.com', 'avt5.jpg', 1, NULL, 0),
('jungdung2004', 'Hồ Nguyễn Quốc Dũng', '902464e5ee5bbf45f57e32ed3a66d1ef', 'jungdung2004@gmail.com', '1766470284_694a328c7f272.jpg', 0, '109039166477257996447', 0),
('khachhang1', 'Nguyễn Văn Tài', 'e10adc3949ba59abbe56e057f20f883e', 'duy2912www@gmail.com', 'avt6.jpg', 0, '106649780835115078066', 0);

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
-- Chỉ mục cho bảng `document_likes`
--
ALTER TABLE `document_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_id` (`document_id`,`user_id`);

--
-- Chỉ mục cho bảng `document_uploads`
--
ALTER TABLE `document_uploads`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Chỉ mục cho bảng `saved_documents`
--
ALTER TABLE `saved_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`username`,`document_id`);

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
  MODIFY `document_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `document_likes`
--
ALTER TABLE `document_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `document_uploads`
--
ALTER TABLE `document_uploads`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `saved_documents`
--
ALTER TABLE `saved_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `slideshows`
--
ALTER TABLE `slideshows`
  MODIFY `slideshow_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
