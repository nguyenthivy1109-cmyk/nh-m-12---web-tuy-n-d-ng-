-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: localhost    Database: tuyen_dung_db
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `admin_id` bigint NOT NULL AUTO_INCREMENT,
  `tai_khoan_id` bigint NOT NULL,
  `ho_ten` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ghi_chu` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  KEY `FK_admins_tai_khoans` (`tai_khoan_id`),
  CONSTRAINT `FK_admins_tai_khoans` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoans` (`tai_khoan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,1,'Nguyễn Văn Admin','Quản trị viên hệ thống','2025-11-10 20:09:07','2025-11-10 20:09:07'),(2,2,'Trần Thị Admin','Quản trị viên hệ thống','2025-11-10 20:09:07','2025-11-10 20:09:07');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cong_tys`
--

DROP TABLE IF EXISTS `cong_tys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cong_tys` (
  `cong_ty_id` bigint NOT NULL AUTO_INCREMENT,
  `ten_cong_ty` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ma_so_thue` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `nganh_nghe` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `quy_mo` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `logo_url` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `bia_url` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `gioi_thieu` text COLLATE utf8mb4_unicode_ci,
  `dia_chi_tru_so` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `xoa_luc` datetime DEFAULT NULL,
  PRIMARY KEY (`cong_ty_id`),
  UNIQUE KEY `UQ_cong_tys_slug` (`slug`),
  KEY `IX_cong_tys_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cong_tys`
--

LOCK TABLES `cong_tys` WRITE;
/*!40000 ALTER TABLE `cong_tys` DISABLE KEYS */;
INSERT INTO `cong_tys` VALUES (1,'FPT Software','fpt-software','0101245184','https://www.fpt-software.com','Công nghệ thông tin','5000+ nhân viên','/uploads/logos/fpt-logo.png','/uploads/banners/fpt-banner.jpg','FPT Software là công ty hàng đầu về công nghệ thông tin tại Việt Nam, chuyên cung cấp các dịch vụ phần mềm và giải pháp công nghệ.','17 Duy Tân, Cầu Giấy, Hà Nội','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(2,'Viettel Solutions','viettel-solutions','0102345678','https://viettelsolutions.vn','Viễn thông - Công nghệ','3000+ nhân viên','/uploads/logos/viettel-logo.png','/uploads/banners/viettel-banner.jpg','Viettel Solutions là đơn vị chuyên về giải pháp công nghệ và dịch vụ viễn thông, thuộc Tập đoàn Công nghiệp - Viễn thông Quân đội.','1 Giang Văn Minh, Ba Đình, Hà Nội','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(3,'VNG Corporation','vng-corporation','0103456789','https://vng.com.vn','Công nghệ - Internet','2000+ nhân viên','/uploads/logos/vng-logo.png','/uploads/banners/vng-banner.jpg','VNG là công ty Internet và công nghệ hàng đầu Việt Nam, sở hữu nhiều sản phẩm nổi tiếng như Zalo, Zing MP3, Zing News.','182 Lê Đại Hành, Phường 15, Quận 11, TP. Hồ Chí Minh','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(4,'Tiki Corporation','tiki-corporation','0104567890','https://tiki.vn','Thương mại điện tử','1500+ nhân viên','/uploads/logos/tiki-logo.png','/uploads/banners/tiki-banner.jpg','Tiki là một trong những sàn thương mại điện tử hàng đầu Việt Nam, chuyên cung cấp đa dạng sản phẩm và dịch vụ.','52 Út Tịch, Phường 4, Quận Tân Bình, TP. Hồ Chí Minh','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(5,'Toshiba Vietnam','toshiba-vietnam','0105678901','https://www.toshiba.com.vn','Điện tử - Công nghệ','1000+ nhân viên','/uploads/logos/toshiba-logo.png','/uploads/banners/toshiba-banner.jpg','Toshiba Vietnam là chi nhánh của tập đoàn Toshiba tại Việt Nam, chuyên sản xuất và phân phối các sản phẩm điện tử và công nghệ.','364 Cộng Hòa, Phường 13, Quận Tân Bình, TP. Hồ Chí Minh','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(6,'BOSCH VIET NAM','bosch-viet-nam','3603119522','https://www.bosch.com.vn/','Đa ngành nghề','6500+ nhân viên','/uploads/logos/img_691200215f5744.77327799_1762787361.png','/uploads/banners/img_6912002e8988f6.27030782_1762787374.jpg','Toshiba Vietnam là chi nhánh của tập đoàn Toshiba tại Việt Nam, chuyên sản xuất và phân phối các sản phẩm điện tử và công nghệ.','Khu Công nghiệp Long Thành, Xã An Phước, Đồng Nai, Việt Nam','2025-11-10 21:47:19','2025-11-10 22:09:35',NULL);
/*!40000 ALTER TABLE `cong_tys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dinh_kems`
--

DROP TABLE IF EXISTS `dinh_kems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dinh_kems` (
  `dk_id` bigint NOT NULL AUTO_INCREMENT,
  `chu_so_huu_tai_khoan_id` bigint NOT NULL,
  `tep_url` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ten_tep` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `doi_tuong` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'CV, Logo, Banner, Certificate, etc.',
  `doi_tuong_id` bigint NOT NULL COMMENT 'ID của đối tượng liên quan',
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dk_id`),
  KEY `FK_dinh_kems_tai_khoans` (`chu_so_huu_tai_khoan_id`),
  KEY `IX_dinh_kems_doi_tuong` (`doi_tuong`,`doi_tuong_id`),
  KEY `IX_dinh_kems_chu_so_huu` (`chu_so_huu_tai_khoan_id`),
  CONSTRAINT `FK_dinh_kems_tai_khoans` FOREIGN KEY (`chu_so_huu_tai_khoan_id`) REFERENCES `tai_khoans` (`tai_khoan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dinh_kems`
--

LOCK TABLES `dinh_kems` WRITE;
/*!40000 ALTER TABLE `dinh_kems` DISABLE KEYS */;
INSERT INTO `dinh_kems` VALUES (1,8,'/uploads/cvs/nguyen-van-an-cv.pdf','Nguyen_Van_An_CV.pdf','application/pdf','CV',1,'2025-11-05 20:09:07'),(2,9,'/uploads/cvs/tran-van-binh-cv.pdf','Tran_Van_Binh_CV.pdf','application/pdf','CV',2,'2025-11-06 20:09:07'),(3,10,'/uploads/cvs/le-thi-cam-cv.pdf','Le_Thi_Cam_CV.pdf','application/pdf','CV',3,'2025-11-07 20:09:07'),(4,3,'/uploads/logos/fpt-logo.png','FPT_Logo.png','image/png','Logo',1,'2025-11-10 20:09:07'),(5,4,'/uploads/logos/viettel-logo.png','Viettel_Logo.png','image/png','Logo',2,'2025-11-10 20:09:07'),(6,5,'/uploads/logos/vng-logo.png','VNG_Logo.png','image/png','Logo',3,'2025-11-10 20:09:07');
/*!40000 ALTER TABLE `dinh_kems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kn_tu_dien`
--

DROP TABLE IF EXISTS `kn_tu_dien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kn_tu_dien` (
  `kn_id` bigint NOT NULL AUTO_INCREMENT,
  `ten_kn` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`kn_id`),
  UNIQUE KEY `UQ_kn_tu_dien_ten_kn` (`ten_kn`),
  UNIQUE KEY `UQ_kn_tu_dien_slug` (`slug`),
  KEY `IX_kn_tu_dien_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kn_tu_dien`
--

LOCK TABLES `kn_tu_dien` WRITE;
/*!40000 ALTER TABLE `kn_tu_dien` DISABLE KEYS */;
INSERT INTO `kn_tu_dien` VALUES (1,'PHP','php'),(2,'JavaScript','javascript'),(3,'HTML/CSS','html-css'),(4,'MySQL','mysql'),(5,'Laravel','laravel'),(6,'React','react'),(7,'Node.js','nodejs'),(8,'Python','python'),(9,'Java','java'),(10,'C#','csharp'),(11,'.NET','dotnet'),(12,'SQL Server','sql-server'),(13,'PostgreSQL','postgresql'),(14,'MongoDB','mongodb'),(15,'Git','git'),(16,'Docker','docker'),(17,'AWS','aws'),(18,'Azure','azure'),(19,'Angular','angular'),(20,'Vue.js','vuejs'),(21,'TypeScript','typescript'),(22,'Bootstrap','bootstrap'),(23,'jQuery','jquery'),(24,'REST API','rest-api'),(25,'GraphQL','graphql'),(26,'Microservices','microservices'),(27,'CI/CD','cicd'),(28,'Linux','linux'),(29,'Agile','agile'),(30,'Scrum','scrum');
/*!40000 ALTER TABLE `kn_tu_dien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nha_tuyen_dungs`
--

DROP TABLE IF EXISTS `nha_tuyen_dungs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nha_tuyen_dungs` (
  `nha_td_id` bigint NOT NULL AUTO_INCREMENT,
  `tai_khoan_id` bigint NOT NULL,
  `cong_ty_id` bigint DEFAULT NULL,
  `ho_ten` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `chuc_danh` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email_cong_viec` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nha_td_id`),
  KEY `FK_nha_tuyen_dungs_tai_khoans` (`tai_khoan_id`),
  KEY `FK_nha_tuyen_dungs_cong_tys` (`cong_ty_id`),
  CONSTRAINT `FK_nha_tuyen_dungs_cong_tys` FOREIGN KEY (`cong_ty_id`) REFERENCES `cong_tys` (`cong_ty_id`) ON DELETE SET NULL,
  CONSTRAINT `FK_nha_tuyen_dungs_tai_khoans` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoans` (`tai_khoan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nha_tuyen_dungs`
--

LOCK TABLES `nha_tuyen_dungs` WRITE;
/*!40000 ALTER TABLE `nha_tuyen_dungs` DISABLE KEYS */;
INSERT INTO `nha_tuyen_dungs` VALUES (1,3,1,'Nguyễn Thị Hoa','Trưởng phòng Nhân sự','hoa.nguyen@fpt-software.com','2025-11-10 20:09:07','2025-11-10 20:09:07'),(2,4,2,'Trần Văn Minh','Chuyên viên Tuyển dụng','minh.tran@viettelsolutions.vn','2025-11-10 20:09:07','2025-11-10 20:09:07'),(3,5,3,'Lê Thị Lan','HR Manager','lan.le@vng.com.vn','2025-11-10 20:09:07','2025-11-10 20:09:07'),(4,6,4,'Phạm Văn Đức','Talent Acquisition','duc.pham@tiki.vn','2025-11-10 20:09:07','2025-11-10 20:09:07'),(5,7,5,'Hoàng Thị Mai','Recruitment Specialist','mai.hoang@toshiba.com.vn','2025-11-10 20:09:07','2025-11-10 20:09:07'),(6,17,6,'Hoàng Chiêu Giao','Staff Manager','hoanggiao@bosch.hr.vn','2025-11-10 21:05:28','2025-11-10 21:53:34');
/*!40000 ALTER TABLE `nha_tuyen_dungs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_khoans`
--

DROP TABLE IF EXISTS `tai_khoans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tai_khoans` (
  `tai_khoan_id` bigint NOT NULL AUTO_INCREMENT,
  `ten_dn` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `mat_khau_hash` varbinary(256) NOT NULL,
  `mat_khau_salt` varbinary(128) DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `dien_thoai` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `vai_tro_id` int NOT NULL COMMENT '1: Admin, 2: Nhà tuyển dụng, 3: Ứng viên',
  `kich_hoat` tinyint(1) NOT NULL DEFAULT '1',
  `dang_nhap_cuoi_luc` datetime DEFAULT NULL,
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `xoa_luc` datetime DEFAULT NULL,
  PRIMARY KEY (`tai_khoan_id`),
  UNIQUE KEY `UQ_tai_khoans_ten_dn` (`ten_dn`),
  UNIQUE KEY `UQ_tai_khoans_email` (`email`),
  KEY `IX_tai_khoans_email` (`email`),
  KEY `IX_tai_khoans_vai_tro_id` (`vai_tro_id`),
  KEY `IX_tai_khoans_kich_hoat` (`kich_hoat`),
  CONSTRAINT `CHK_tai_khoans_vai_tro` CHECK ((`vai_tro_id` in (1,2,3)))
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_khoans`
--

LOCK TABLES `tai_khoans` WRITE;
/*!40000 ALTER TABLE `tai_khoans` DISABLE KEYS */;
INSERT INTO `tai_khoans` VALUES (1,'admin',_binary '$2y$12$JswzgVsZSDHhfWqFHFSD6OSt/Njeo3Js3O7SbgJ.9mjTUHNfEfuYG',NULL,'admin@tuyendung.com','0901234567',1,1,'2025-11-10 21:00:01','2025-11-10 20:09:07','2025-11-10 21:00:01',NULL),(2,'admin2',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'admin2@tuyendung.com','0901234568',1,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(3,'ntd_fpt',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'tuyendung@fpt.com.vn','0901111111',2,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(4,'ntd_viettel',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'tuyendung@viettel.com.vn','0902222222',2,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(5,'ntd_vng',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'hr@vng.com.vn','0903333333',2,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(6,'ntd_tiki',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'careers@tiki.vn','0904444444',2,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(7,'ntd_toshiba',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'hr@toshiba.com.vn','0905555555',2,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(8,'uv_nguyenvana',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'nguyenvana@gmail.com','0911111111',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(9,'uv_tranvanb',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'tranvanb@gmail.com','0912222222',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(10,'uv_lethic',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'lethic@yahoo.com','0913333333',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(11,'uv_phamd',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'phamd@gmail.com','0914444444',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(12,'uv_hoange',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'hoange@gmail.com','0915555555',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(13,'uv_vuf',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'vuf@gmail.com','0916666666',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(14,'uv_daog',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'daog@gmail.com','0917777777',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(15,'uv_buih',_binary '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,'buih@gmail.com','0918888888',3,1,'2025-11-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(16,'admin_demo',_binary '$2y$12$Q3p4tWyQjjYP1lVK9rzqS.6sYb/6MRM9ACXbJaCG0L0cJlbJkbDG2',NULL,'admin@demo.com','0900000001',1,1,'2025-11-10 20:52:32','2025-11-10 20:35:59','2025-11-10 20:52:32',NULL),(17,'ntd_demo',_binary '$2y$12$pkRY9rrpaoyGx4pheuyb4uEhD4BOzRBqFNYzr4HSOZKglAhcTq3JS',NULL,'ntd@demo.com','0900000002',2,1,'2025-11-10 23:10:41','2025-11-10 20:36:00','2025-11-10 23:10:41',NULL),(18,'uv_demo',_binary '$2y$12$uqY8JP2V73tXrKa/11juCed9G8sWn1V0WXHMNGCU5r/mRZKM75Wse',NULL,'uv@demo.com','0900000003',3,1,'2025-11-10 23:10:22','2025-11-10 20:36:01','2025-11-10 23:10:22',NULL);
/*!40000 ALTER TABLE `tai_khoans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thong_baos`
--

DROP TABLE IF EXISTS `thong_baos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thong_baos` (
  `tb_id` bigint NOT NULL AUTO_INCREMENT,
  `tai_khoan_id` bigint NOT NULL,
  `loai_tb` int NOT NULL COMMENT '1: Ứng tuyển mới, 2: Cập nhật trạng thái, 3: Tin nhắn mới, 4: Thông báo hệ thống',
  `tieu_de` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `noi_dung` text COLLATE utf8mb4_unicode_ci,
  `da_doc` tinyint(1) NOT NULL DEFAULT '0',
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tb_id`),
  KEY `FK_thong_baos_tai_khoans` (`tai_khoan_id`),
  KEY `IX_thong_baos_da_doc` (`da_doc`),
  KEY `IX_thong_baos_tao_luc` (`tao_luc`),
  CONSTRAINT `FK_thong_baos_tai_khoans` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoans` (`tai_khoan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thong_baos`
--

LOCK TABLES `thong_baos` WRITE;
/*!40000 ALTER TABLE `thong_baos` DISABLE KEYS */;
INSERT INTO `thong_baos` VALUES (1,3,1,'Có ứng viên mới ứng tuyển','Nguyễn Văn An đã ứng tuyển vào vị trí Lập trình viên PHP/Laravel',0,'2025-11-05 20:09:07'),(2,3,1,'Có ứng viên mới ứng tuyển','Lê Thị Cẩm đã ứng tuyển vào vị trí Lập trình viên PHP/Laravel',1,'2025-11-07 20:09:07'),(3,3,2,'Cập nhật trạng thái ứng tuyển','Ứng tuyển của Lê Thị Cẩm đã được đánh giá là Phù hợp',0,'2025-11-09 20:09:07'),(4,4,1,'Có ứng viên mới ứng tuyển','Trần Văn Bình đã ứng tuyển vào vị trí Frontend Developer',1,'2025-11-06 20:09:07'),(5,5,1,'Có ứng viên mới ứng tuyển','Phạm Văn Dũng đã ứng tuyển vào vị trí Java Developer',0,'2025-11-04 20:09:07'),(6,5,2,'Cập nhật trạng thái ứng tuyển','Ứng tuyển của Phạm Văn Dũng đã được đánh giá là Phù hợp',1,'2025-11-08 20:09:07'),(7,7,2,'Cập nhật trạng thái ứng tuyển','Ứng tuyển của Vũ Văn Phong đã được mời phỏng vấn',0,'2025-11-09 20:09:07'),(8,8,2,'Cập nhật trạng thái ứng tuyển','Ứng tuyển của bạn vào vị trí Lập trình viên PHP/Laravel đã được xem',1,'2025-11-06 20:09:07'),(9,10,2,'Cập nhật trạng thái ứng tuyển','Ứng tuyển của bạn vào vị trí Lập trình viên PHP/Laravel đã được đánh giá là Phù hợp',0,'2025-11-09 20:09:07'),(10,9,2,'Cập nhật trạng thái ứng tuyển','Ứng tuyển của bạn vào vị trí Frontend Developer đã được xem',1,'2025-11-07 20:09:07'),(11,13,2,'Cập nhật trạng thái ứng tuyển','Ứng tuyển của bạn vào vị trí DevOps Engineer đã được mời phỏng vấn',0,'2025-11-09 20:09:07'),(12,1,4,'Thông báo hệ thống','Chào mừng bạn đến với hệ thống tuyển dụng!',1,'2025-10-31 20:09:07');
/*!40000 ALTER TABLE `thong_baos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tin_kn`
--

DROP TABLE IF EXISTS `tin_kn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tin_kn` (
  `tin_id` bigint NOT NULL,
  `kn_id` bigint NOT NULL,
  `muc_quan_trong` int DEFAULT '1' COMMENT '1: Bắt buộc, 2: Quan trọng, 3: Mong muốn',
  PRIMARY KEY (`tin_id`,`kn_id`),
  KEY `FK_tin_kn_kn_tu_dien` (`kn_id`),
  CONSTRAINT `FK_tin_kn_kn_tu_dien` FOREIGN KEY (`kn_id`) REFERENCES `kn_tu_dien` (`kn_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_tin_kn_tin_td` FOREIGN KEY (`tin_id`) REFERENCES `tin_td` (`tin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tin_kn`
--

LOCK TABLES `tin_kn` WRITE;
/*!40000 ALTER TABLE `tin_kn` DISABLE KEYS */;
INSERT INTO `tin_kn` VALUES (1,1,1),(1,4,2),(1,5,1),(1,15,2),(1,24,3),(2,2,1),(2,3,2),(2,6,1),(2,21,1),(2,24,2),(2,26,3),(3,9,1),(3,11,1),(3,15,2),(3,16,2),(3,17,2),(3,27,2),(4,2,2),(4,6,1),(4,8,1),(4,14,2),(4,15,2),(4,24,2),(5,4,2),(5,8,1),(5,15,2),(5,24,2),(6,16,1),(6,17,1),(6,18,3),(6,27,1),(6,28,2),(7,2,1),(7,6,1),(7,15,2),(7,24,2),(8,22,2);
/*!40000 ALTER TABLE `tin_kn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tin_nhans`
--

DROP TABLE IF EXISTS `tin_nhans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tin_nhans` (
  `tn_id` bigint NOT NULL AUTO_INCREMENT,
  `nhan_boi_tai_khoan_id` bigint NOT NULL COMMENT 'Người nhận',
  `gui_boi_tai_khoan_id` bigint NOT NULL COMMENT 'Người gửi',
  `ung_tuyen_id` bigint NOT NULL,
  `noi_dung` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `da_doc` tinyint(1) NOT NULL DEFAULT '0',
  `gui_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tn_id`),
  KEY `FK_tin_nhans_nhan_boi` (`nhan_boi_tai_khoan_id`),
  KEY `FK_tin_nhans_gui_boi` (`gui_boi_tai_khoan_id`),
  KEY `FK_tin_nhans_ung_tuyens` (`ung_tuyen_id`),
  KEY `IX_tin_nhans_da_doc` (`da_doc`),
  CONSTRAINT `FK_tin_nhans_gui_boi` FOREIGN KEY (`gui_boi_tai_khoan_id`) REFERENCES `tai_khoans` (`tai_khoan_id`) ON DELETE RESTRICT,
  CONSTRAINT `FK_tin_nhans_nhan_boi` FOREIGN KEY (`nhan_boi_tai_khoan_id`) REFERENCES `tai_khoans` (`tai_khoan_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_tin_nhans_ung_tuyens` FOREIGN KEY (`ung_tuyen_id`) REFERENCES `ung_tuyens` (`ung_tuyen_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tin_nhans`
--

LOCK TABLES `tin_nhans` WRITE;
/*!40000 ALTER TABLE `tin_nhans` DISABLE KEYS */;
INSERT INTO `tin_nhans` VALUES (1,8,3,1,'Chào bạn Nguyễn Văn An, chúng tôi đã nhận được CV của bạn. Chúng tôi sẽ xem xét và phản hồi sớm nhất có thể.',1,'2025-11-06 20:09:07'),(2,10,3,2,'Chào bạn Lê Thị Cẩm, chúng tôi đánh giá cao hồ sơ của bạn. Bạn có thể sắp xếp thời gian để phỏng vấn không?',0,'2025-11-09 20:09:07'),(3,9,4,3,'Cảm ơn bạn đã ứng tuyển. Chúng tôi sẽ liên hệ lại với bạn sớm.',1,'2025-11-07 20:09:07'),(4,11,5,5,'Chào bạn Phạm Văn Dũng, chúng tôi rất ấn tượng với kinh nghiệm của bạn. Bạn có thể cho chúng tôi biết thêm về dự án microservices bạn đã làm không?',1,'2025-11-08 20:09:07'),(5,13,7,8,'Chào bạn Vũ Văn Phong, chúng tôi muốn mời bạn tham gia phỏng vấn vào tuần tới. Bạn có thể sắp xếp thời gian không?',0,'2025-11-09 20:09:07'),(6,3,8,1,'Cảm ơn bạn đã xem hồ sơ của tôi. Tôi rất mong được làm việc tại FPT Software.',1,'2025-11-07 20:09:07'),(7,3,10,2,'Cảm ơn bạn đã đánh giá cao hồ sơ của tôi. Tôi có thể sắp xếp phỏng vấn vào thứ 3 hoặc thứ 5 tuần này.',1,'2025-11-10 08:09:07'),(8,5,11,5,'Cảm ơn bạn. Tôi đã từng làm dự án microservices cho một ngân hàng, sử dụng Spring Cloud và Docker. Tôi có thể gửi thêm thông tin chi tiết nếu bạn cần.',1,'2025-11-09 20:09:07');
/*!40000 ALTER TABLE `tin_nhans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tin_td`
--

DROP TABLE IF EXISTS `tin_td`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tin_td` (
  `tin_id` bigint NOT NULL AUTO_INCREMENT,
  `cong_ty_id` bigint NOT NULL,
  `nha_td_id` bigint NOT NULL,
  `tieu_de` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `mo_ta` text COLLATE utf8mb4_unicode_ci,
  `yeu_cau` text COLLATE utf8mb4_unicode_ci,
  `noi_lam_viec` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `hinh_thuc_lv` int DEFAULT NULL COMMENT '1: Full-time, 2: Part-time, 3: Remote, 4: Freelance',
  `che_do_lv` int DEFAULT NULL COMMENT '1: Nhân viên, 2: Quản lý, 3: Giám đốc',
  `luong_min` decimal(18,2) DEFAULT NULL,
  `luong_max` decimal(18,2) DEFAULT NULL,
  `tien_te` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'VND',
  `cap_do_kn` int DEFAULT NULL COMMENT '1: Mới tốt nghiệp, 2: 1-3 năm, 3: 3-5 năm, 4: 5+ năm',
  `so_luong` int NOT NULL DEFAULT '1',
  `trang_thai_tin` int NOT NULL DEFAULT '0' COMMENT '0: Nháp, 1: Đang tuyển, 2: Tạm dừng, 3: Đã đóng',
  `dang_luc` datetime DEFAULT NULL,
  `het_han_luc` datetime DEFAULT NULL,
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `xoa_luc` datetime DEFAULT NULL,
  PRIMARY KEY (`tin_id`),
  UNIQUE KEY `UQ_tin_td_slug` (`slug`),
  KEY `FK_tin_td_cong_tys` (`cong_ty_id`),
  KEY `FK_tin_td_nha_tuyen_dungs` (`nha_td_id`),
  KEY `IX_tin_td_trang_thai_tin` (`trang_thai_tin`),
  KEY `IX_tin_td_dang_luc` (`dang_luc`),
  KEY `IX_tin_td_het_han_luc` (`het_han_luc`),
  KEY `IX_tin_td_slug` (`slug`),
  CONSTRAINT `FK_tin_td_cong_tys` FOREIGN KEY (`cong_ty_id`) REFERENCES `cong_tys` (`cong_ty_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_tin_td_nha_tuyen_dungs` FOREIGN KEY (`nha_td_id`) REFERENCES `nha_tuyen_dungs` (`nha_td_id`) ON DELETE CASCADE,
  CONSTRAINT `CHK_tin_td_luong` CHECK (((`luong_max` >= `luong_min`) or (`luong_max` is null) or (`luong_min` is null)))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tin_td`
--

LOCK TABLES `tin_td` WRITE;
/*!40000 ALTER TABLE `tin_td` DISABLE KEYS */;
INSERT INTO `tin_td` VALUES (1,1,1,'Lập trình viên PHP/Laravel','lap-trinh-vien-php-laravel-fpt','Tìm kiếm lập trình viên PHP/Laravel có kinh nghiệm để tham gia phát triển các dự án web application lớn. Cơ hội làm việc trong môi trường chuyên nghiệp, học hỏi nhiều công nghệ mới.','Tốt nghiệp Đại học chuyên ngành CNTT hoặc tương đương. Có ít nhất 2 năm kinh nghiệm phát triển web với PHP. Thành thạo Laravel framework. Có kiến thức về MySQL, Git, REST API.','Hà Nội',1,1,15000000.00,25000000.00,'VND',2,5,1,'2025-11-10 20:09:07','2025-12-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(2,1,1,'Frontend Developer - React/TypeScript','frontend-developer-react-typescript-fpt','Tuyển dụng Frontend Developer có kinh nghiệm với React và TypeScript để phát triển các ứng dụng web hiện đại. Làm việc với team năng động, sáng tạo.','Có 2-3 năm kinh nghiệm phát triển frontend. Thành thạo React, TypeScript, HTML/CSS, JavaScript. Có kiến thức về Redux, GraphQL. Kinh nghiệm làm việc với REST API.','Hà Nội',1,1,18000000.00,30000000.00,'VND',2,3,1,'2025-11-10 20:09:07','2025-12-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(3,2,2,'Java Developer - Microservices','java-developer-microservices-viettel','Tuyển dụng Java Developer có kinh nghiệm về microservices để tham gia phát triển hệ thống enterprise. Môi trường làm việc chuyên nghiệp, đãi ngộ tốt.','Tốt nghiệp Đại học CNTT. Có 3-5 năm kinh nghiệm phát triển Java. Thành thạo Spring Boot, Spring Cloud. Có kiến thức về microservices architecture, Docker, Kubernetes.','Hà Nội',1,2,20000000.00,35000000.00,'VND',3,2,1,'2025-11-10 20:09:07','2025-12-25 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(4,3,3,'Full-stack Developer - Node.js/React','fullstack-developer-nodejs-react-vng','Tuyển dụng Full-stack Developer để phát triển các sản phẩm Internet hàng đầu. Cơ hội làm việc với các công nghệ mới nhất, team trẻ trung, năng động.','Có 2-4 năm kinh nghiệm full-stack development. Thành thạo Node.js, React, MongoDB hoặc MySQL. Có kiến thức về REST API, GraphQL. Kinh nghiệm làm việc với cloud services.','TP. Hồ Chí Minh',1,1,20000000.00,35000000.00,'VND',2,4,1,'2025-11-10 20:09:07','2025-12-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(5,4,4,'Python Developer - Data Science','python-developer-data-science-tiki','Tuyển dụng Python Developer có kiến thức về Data Science để phát triển các tính năng AI/ML cho nền tảng thương mại điện tử. Môi trường startup năng động.','Có 2-3 năm kinh nghiệm với Python. Có kiến thức về Data Science, Machine Learning. Thành thạo Django hoặc Flask. Kinh nghiệm với pandas, numpy, scikit-learn.','TP. Hồ Chí Minh',1,1,18000000.00,30000000.00,'VND',2,2,1,'2025-11-10 20:09:07','2025-12-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(6,5,5,'DevOps Engineer','devops-engineer-toshiba','Tuyển dụng DevOps Engineer để quản lý và tự động hóa infrastructure. Làm việc với các công nghệ cloud và container hiện đại.','Có 3-5 năm kinh nghiệm DevOps. Thành thạo Docker, Kubernetes, CI/CD. Có kiến thức về AWS, Azure. Kinh nghiệm với Terraform, Ansible. Biết scripting (Bash, Python).','TP. Hồ Chí Minh',1,2,25000000.00,40000000.00,'VND',3,2,1,'2025-11-10 20:09:07','2025-12-25 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(7,3,3,'Mobile Developer - React Native','mobile-developer-react-native-vng','Tuyển dụng Mobile Developer để phát triển ứng dụng di động đa nền tảng. Làm việc với các sản phẩm có hàng triệu người dùng.','Có 2-3 năm kinh nghiệm phát triển mobile. Thành thạo React Native. Có kiến thức về iOS và Android development. Kinh nghiệm với Redux, REST API, Firebase.','TP. Hồ Chí Minh',1,1,18000000.00,30000000.00,'VND',2,3,1,'2025-11-10 20:09:07','2025-12-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(8,1,1,'UI/UX Designer','ui-ux-designer-fpt','Tuyển dụng UI/UX Designer để thiết kế giao diện và trải nghiệm người dùng cho các sản phẩm phần mềm. Môi trường sáng tạo, cơ hội phát triển.','Có 2-3 năm kinh nghiệm thiết kế UI/UX. Thành thạo Figma, Adobe XD, Sketch. Có portfolio đẹp. Hiểu biết về design system, user research. Có tư duy thiết kế tốt.','Hà Nội',1,1,12000000.00,20000000.00,'VND',2,2,1,'2025-11-10 20:09:07','2025-12-10 20:09:07','2025-11-10 20:09:07','2025-11-10 20:09:07',NULL),(9,6,6,'Translation Intern (Japanese + English Required)','translation-intern-japanese-english-required','Receive specification documents from customer.\r\nImport the content of specification in various formats (word, excel, pdf, matrix...) into Doors system, manage version, set attribute and layout as per customer requirement.\r\nEnsure consistency of attribute, layouts for all modules.\r\nReflect changes of specification into system when having updates.\r\nDevelop and manage project metrics.\r\nBe in charge of other activities such as system configuration management, baseline copy, tracking report...\r\nTranslate technical documents JP-EN','Bachelor\'s degree in Linguistics, Business Administration is referred.\r\nLogical and analytical thinking.\r\nMust be undergraduate student at university with Economics, Business management or Linguistic background\r\nAble to work full-time in 6 months.\r\nGood communication, verbal and written both in English and Japanese (N3 or higher is preferable)\r\nGood at using Microsoft Office applications (PowerPoint, Word, Teams, SharePoint, Power BI)\r\nWilling to learn, high teamwork spirit, proactive mindset\r\nDiligent, enthusiastic, and self-confident to meet deadlines\r\nPossess Time Management skill and Follow-up skills\r\nWell-organized, detail-oriented, and ability to manage multiple tasks simultaneously.\r\nGood at English communication.\r\nJapanese N2 or above.','Hồ Chí Minh: 32 Đ. Tân Thắng, Sơn Kỳ,, Tân Phú',1,1,10000000.00,NULL,'VND',2,10,1,'2025-11-10 22:28:33','2025-11-16 00:00:00','2025-11-10 22:28:33','2025-11-10 22:28:33',NULL),(10,6,6,'JavaScript Developer Intern','javascript-developer-intern','Analyze, design, and develop business workflows application on low code no code platform\r\nImplement custom actions and logic using JavaScript where advance feature / logic / validation required.\r\nCollaborate with business analysts and stakeholders to ensure workflows meet business requirements.\r\nTake ownership and follow up\r\nProfessional in communication and deliverables\r\nResult and success oriented\r\nHigh energy and passionate\r\nTeamwork spirit & high result oriented\r\nAmbitious and focus on action.\r\nBe curious, wiling to learn and get new assignments or projects.','Recommendation Letter for 6-months internship program from your University is a MUST.\r\nSenior or final year student pursuing a Bachelor\'s or Master degree in Computer Science, Information Technology, or a related subject.\r\nBasic understanding of JavaScript and HTML.\r\nFamiliarity with debugging JavaScript applications.\r\nStrong problem-solving skills and attention to detail.\r\nAbility to read and comprehend existing code.\r\nWillingness to learn and adapt to different coding practices.\r\nPrior experience with web development (coursework, personal projects, or internships) is a plus.','Hồ Chí Minh: 364 Cộng Hòa, Tân Bình',1,1,5500000.00,NULL,'VND',1,5,3,'2025-11-10 23:14:59','2025-11-17 00:00:00','2025-11-10 23:13:24','2025-11-10 23:16:02',NULL);
/*!40000 ALTER TABLE `tin_td` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ung_tuyens`
--

DROP TABLE IF EXISTS `ung_tuyens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ung_tuyens` (
  `ung_tuyen_id` bigint NOT NULL AUTO_INCREMENT,
  `tin_id` bigint NOT NULL,
  `ung_vien_id` bigint NOT NULL,
  `cv_id` bigint DEFAULT NULL COMMENT 'Tham chiếu đến dinh_kems',
  `thu_ung_tuyen` text COLLATE utf8mb4_unicode_ci,
  `nguon` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `trang_thai_ut` int NOT NULL DEFAULT '0' COMMENT '0: Chờ xem, 1: Đã xem, 2: Phù hợp, 3: Không phù hợp, 4: Đã mời phỏng vấn, 5: Đã từ chối',
  `nop_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_tt_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ung_tuyen_id`),
  UNIQUE KEY `UQ_ung_tuyens_tin_ung_vien` (`tin_id`,`ung_vien_id`),
  KEY `FK_ung_tuyens_tin_td` (`tin_id`),
  KEY `FK_ung_tuyens_ung_viens` (`ung_vien_id`),
  KEY `IX_ung_tuyens_trang_thai_ut` (`trang_thai_ut`),
  KEY `IX_ung_tuyens_nop_luc` (`nop_luc`),
  CONSTRAINT `FK_ung_tuyens_tin_td` FOREIGN KEY (`tin_id`) REFERENCES `tin_td` (`tin_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_ung_tuyens_ung_viens` FOREIGN KEY (`ung_vien_id`) REFERENCES `ung_viens` (`ung_vien_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ung_tuyens`
--

LOCK TABLES `ung_tuyens` WRITE;
/*!40000 ALTER TABLE `ung_tuyens` DISABLE KEYS */;
INSERT INTO `ung_tuyens` VALUES (1,1,1,NULL,'Tôi rất quan tâm đến vị trí Lập trình viên PHP/Laravel tại FPT Software. Với 3 năm kinh nghiệm phát triển web bằng PHP và Laravel, tôi tin rằng mình phù hợp với yêu cầu của công ty.','Website',1,'2025-11-05 20:09:07','2025-11-05 20:09:07','2025-11-05 20:09:07','2025-11-10 20:09:07'),(2,1,3,NULL,'Tôi là full-stack developer và rất quan tâm đến vị trí này. Tôi có kinh nghiệm với cả PHP/Laravel và các công nghệ frontend hiện đại.','Website',2,'2025-11-07 20:09:07','2025-11-09 20:09:07','2025-11-07 20:09:07','2025-11-10 20:09:07'),(3,2,2,NULL,'Tôi là Frontend Developer với 2 năm kinh nghiệm React. Rất mong được làm việc tại FPT Software.','Website',1,'2025-11-06 20:09:07','2025-11-06 20:09:07','2025-11-06 20:09:07','2025-11-10 20:09:07'),(4,2,3,NULL,'Tôi có kinh nghiệm full-stack và thành thạo React. Mong được tham gia team của các bạn.','Website',0,'2025-11-08 20:09:07','2025-11-08 20:09:07','2025-11-08 20:09:07','2025-11-10 20:09:07'),(5,3,4,NULL,'Tôi là Senior Java Developer với 5 năm kinh nghiệm. Đã từng làm việc với microservices và Spring Cloud.','Website',2,'2025-11-04 20:09:07','2025-11-08 20:09:07','2025-11-04 20:09:07','2025-11-10 20:09:07'),(6,4,3,NULL,'Tôi là full-stack developer và rất thích môi trường làm việc tại VNG. Mong được tham gia team.','Website',1,'2025-11-07 20:09:07','2025-11-07 20:09:07','2025-11-07 20:09:07','2025-11-10 20:09:07'),(7,5,5,NULL,'Tôi là Python Developer với kiến thức về Data Science. Rất quan tâm đến vị trí tại Tiki.','Website',0,'2025-11-09 20:09:07','2025-11-09 20:09:07','2025-11-09 20:09:07','2025-11-10 20:09:07'),(8,6,6,NULL,'Tôi là DevOps Engineer với 4 năm kinh nghiệm. Thành thạo Docker, Kubernetes và AWS.','Website',4,'2025-11-03 20:09:07','2025-11-09 20:09:07','2025-11-03 20:09:07','2025-11-10 20:09:07'),(9,7,8,NULL,'Tôi là Mobile Developer với kinh nghiệm React Native. Mong được làm việc tại VNG.','Website',1,'2025-11-06 20:09:07','2025-11-06 20:09:07','2025-11-06 20:09:07','2025-11-10 20:09:07'),(10,8,7,NULL,'Tôi là UI/UX Designer với portfolio đẹp. Rất mong được tham gia team thiết kế tại FPT.','Website',0,'2025-11-08 20:09:07','2025-11-08 20:09:07','2025-11-08 20:09:07','2025-11-10 20:09:07');
/*!40000 ALTER TABLE `ung_tuyens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ung_vien_kn`
--

DROP TABLE IF EXISTS `ung_vien_kn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ung_vien_kn` (
  `ung_vien_id` bigint NOT NULL,
  `kn_id` bigint NOT NULL,
  `muc_do` int DEFAULT '1' COMMENT '1: Cơ bản, 2: Trung bình, 3: Khá, 4: Tốt, 5: Chuyên gia',
  PRIMARY KEY (`ung_vien_id`,`kn_id`),
  KEY `FK_ung_vien_kn_kn_tu_dien` (`kn_id`),
  CONSTRAINT `FK_ung_vien_kn_kn_tu_dien` FOREIGN KEY (`kn_id`) REFERENCES `kn_tu_dien` (`kn_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_ung_vien_kn_ung_viens` FOREIGN KEY (`ung_vien_id`) REFERENCES `ung_viens` (`ung_vien_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ung_vien_kn`
--

LOCK TABLES `ung_vien_kn` WRITE;
/*!40000 ALTER TABLE `ung_vien_kn` DISABLE KEYS */;
INSERT INTO `ung_vien_kn` VALUES (1,1,4),(1,4,3),(1,5,4),(1,15,3),(1,24,3),(2,2,5),(2,3,4),(2,6,5),(2,21,4),(2,24,4),(3,1,4),(3,2,4),(3,4,3),(3,6,4),(3,8,4),(3,15,4),(4,9,5),(4,11,4),(4,15,4),(4,16,3),(4,27,4),(5,4,3),(5,8,4),(5,15,3),(6,15,4),(6,16,5),(6,17,5),(6,27,5),(6,28,4),(7,22,4),(8,2,4),(8,6,4),(8,15,3),(8,24,3);
/*!40000 ALTER TABLE `ung_vien_kn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ung_viens`
--

DROP TABLE IF EXISTS `ung_viens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ung_viens` (
  `ung_vien_id` bigint NOT NULL AUTO_INCREMENT,
  `tai_khoan_id` bigint NOT NULL,
  `ho_ten` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `noi_o` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tieu_de_cv` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `gioi_thieu` text COLLATE utf8mb4_unicode_ci,
  `tao_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cap_nhat_luc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ung_vien_id`),
  KEY `FK_ung_viens_tai_khoans` (`tai_khoan_id`),
  CONSTRAINT `FK_ung_viens_tai_khoans` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoans` (`tai_khoan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ung_viens`
--

LOCK TABLES `ung_viens` WRITE;
/*!40000 ALTER TABLE `ung_viens` DISABLE KEYS */;
INSERT INTO `ung_viens` VALUES (1,8,'Nguyễn Văn An','1995-03-15','Nam','Hà Nội','PHP Developer với 3 năm kinh nghiệm','Tôi là một lập trình viên PHP với 3 năm kinh nghiệm trong việc phát triển web application. Tôi có kiến thức sâu về Laravel framework và MySQL database.','2025-11-10 20:09:07','2025-11-10 20:09:07'),(2,9,'Trần Văn Bình','1997-07-20','Nam','TP. Hồ Chí Minh','Frontend Developer - React/JavaScript','Chuyên về phát triển giao diện người dùng với React, JavaScript, HTML/CSS. Có kinh nghiệm làm việc với các dự án lớn.','2025-11-10 20:09:07','2025-11-10 20:09:07'),(3,10,'Lê Thị Cẩm','1996-11-10','Nữ','Đà Nẵng','Full-stack Developer','Full-stack developer với kiến thức về cả frontend và backend. Thành thạo PHP, JavaScript, React, Node.js và các công nghệ web hiện đại.','2025-11-10 20:09:07','2025-11-10 20:09:07'),(4,11,'Phạm Văn Dũng','1994-05-25','Nam','Hà Nội','Senior Java Developer','Java developer với 5 năm kinh nghiệm, chuyên về phát triển enterprise application, microservices và Spring framework.','2025-11-10 20:09:07','2025-11-10 20:09:07'),(5,12,'Hoàng Thị Em','1998-09-30','Nữ','TP. Hồ Chí Minh','Python Developer','Python developer với kiến thức về Django, Flask, data science và machine learning. Có kinh nghiệm làm việc với các dự án AI.','2025-11-10 20:09:07','2025-11-10 20:09:07'),(6,13,'Vũ Văn Phong','1993-12-05','Nam','Hà Nội','DevOps Engineer','DevOps engineer với kinh nghiệm về Docker, Kubernetes, AWS, CI/CD. Chuyên về automation và infrastructure as code.','2025-11-10 20:09:07','2025-11-10 20:09:07'),(7,14,'Đào Thị Giang','1996-02-18','Nữ','TP. Hồ Chí Minh','UI/UX Designer','UI/UX Designer với khả năng thiết kế giao diện đẹp và trải nghiệm người dùng tốt. Thành thạo Figma, Adobe XD, Sketch.','2025-11-10 20:09:07','2025-11-10 20:09:07'),(8,15,'Bùi Văn Hùng','1995-08-22','Nam','Hà Nội','Mobile Developer - React Native','Mobile developer chuyên về React Native, có kinh nghiệm phát triển ứng dụng iOS và Android. Đã phát triển nhiều ứng dụng thành công.','2025-11-10 20:09:07','2025-11-10 20:09:07');
/*!40000 ALTER TABLE `ung_viens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-10 23:18:48
