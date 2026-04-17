-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 17, 2026 at 10:23 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simagang`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id_attendance` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_masuk` time DEFAULT NULL,
  `waktu_keluar` time DEFAULT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') DEFAULT NULL,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id_attendance`, `id_user`, `tanggal`, `waktu_masuk`, `waktu_keluar`, `status`, `keterangan`) VALUES
(1, 4, '2026-04-16', '08:15:00', '17:00:00', 'Hadir', 'Tugas di kantor pusat'),
(2, 4, '2026-04-17', '09:28:06', NULL, NULL, 'Hadir');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_validation`
--

CREATE TABLE `attendance_validation` (
  `id_validation` int NOT NULL,
  `id_attendance` int DEFAULT NULL,
  `id_pembimbing` int DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `attendance_validation`
--

INSERT INTO `attendance_validation` (`id_validation`, `id_attendance`, `id_pembimbing`, `status`) VALUES
(1, 1, 3, 'Disetujui Pembimbing Lapang');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id_company` int NOT NULL,
  `nama_company` varchar(255) NOT NULL,
  `alamat` text,
  `kontak` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id_company`, `nama_company`, `alamat`, `kontak`) VALUES
(1, 'PT. Inovasi Digital', 'Cyber Tower Lt. 10, Jakarta', '021-998877');

-- --------------------------------------------------------

--
-- Table structure for table `daily_journal`
--

CREATE TABLE `daily_journal` (
  `id_journal` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `kegiatan` text,
  `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `catatan_dosen` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `daily_journal`
--

INSERT INTO `daily_journal` (`id_journal`, `id_user`, `tanggal`, `kegiatan`, `status`, `catatan_dosen`) VALUES
(1, 4, '2026-04-16', 'Melakukan testing pada modul autentikasi user.', 'Disetujui', 'Bagus, lanjutkan!');

-- --------------------------------------------------------

--
-- Table structure for table `final_evaluation`
--

CREATE TABLE `final_evaluation` (
  `id_evaluation` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `nilai_laporan` decimal(5,2) DEFAULT NULL,
  `nilai_seminar` decimal(5,2) DEFAULT NULL,
  `komentar` text,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `final_evaluation`
--

INSERT INTO `final_evaluation` (`id_evaluation`, `id_user`, `nilai_akhir`, `nilai_laporan`, `nilai_seminar`, `komentar`, `catatan`) VALUES
(1, 4, '88.00', NULL, NULL, 'Performa sangat baik di lapangan dan laporan tersusun rapi.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `internship_placement`
--

CREATE TABLE `internship_placement` (
  `id_placement` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_company` int DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `internship_placement`
--

INSERT INTO `internship_placement` (`id_placement`, `id_user`, `id_company`, `tanggal_mulai`, `tanggal_selesai`) VALUES
(1, 4, 1, '2026-03-01', '2026-08-31');

-- --------------------------------------------------------

--
-- Table structure for table `journal_validation`
--

CREATE TABLE `journal_validation` (
  `id_jval` int NOT NULL,
  `id_journal` int DEFAULT NULL,
  `id_validator` int DEFAULT NULL,
  `status` enum('Disetujui','Ditolak') DEFAULT NULL,
  `catatan` text,
  `validated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id_profile` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `prodi` varchar(100) DEFAULT NULL,
  `semester` int DEFAULT NULL,
  `nama_perusahaan` varchar(150) DEFAULT NULL,
  `dosen_pembimbing` varchar(150) DEFAULT NULL,
  `id_dosen_pembimbing` int DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `posisi_magang` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id_profile`, `id_user`, `nip`, `nim`, `no_hp`, `alamat`, `prodi`, `semester`, `nama_perusahaan`, `dosen_pembimbing`, `id_dosen_pembimbing`, `foto`, `posisi_magang`) VALUES
(1, 4, NULL, '21000123', '081234567890', 'Jl. Teknik No. 5, Surabaya', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, NULL, '12345678', NULL, '0987654321', 'jalan karimata', NULL, NULL, NULL, NULL, 1, NULL, NULL),
(3, 2, '19850101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id_report` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `jenis_laporan` varchar(100) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` int NOT NULL,
  `nama_role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `nama_role`) VALUES
(1, 'Admin'),
(2, 'Dosen Pembimbing'),
(3, 'Pembimbing Lapang'),
(4, 'Mahasiswa');

-- --------------------------------------------------------

--
-- Table structure for table `roles_permissions`
--

CREATE TABLE `roles_permissions` (
  `id_role_permission` int NOT NULL,
  `id_role` int DEFAULT NULL,
  `permission` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`) VALUES
(1, 'Sistem Admin', 'admin@simagang.com', 'admin123'),
(2, 'Dr. Aris Sudarsono', 'aris@univ.ac.id', 'dosen123'),
(3, 'Bapak Eko Tech', 'eko@company.com', 'lapang123'),
(4, 'Rizky Pratama', 'rizky@student.com', 'mhs123');

-- --------------------------------------------------------

--
-- Table structure for table `users_role`
--

CREATE TABLE `users_role` (
  `id_user_role` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `id_role` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users_role`
--

INSERT INTO `users_role` (`id_user_role`, `id_user`, `id_role`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id_attendance`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `attendance_validation`
--
ALTER TABLE `attendance_validation`
  ADD PRIMARY KEY (`id_validation`),
  ADD KEY `id_attendance` (`id_attendance`),
  ADD KEY `id_pembimbing` (`id_pembimbing`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id_company`);

--
-- Indexes for table `daily_journal`
--
ALTER TABLE `daily_journal`
  ADD PRIMARY KEY (`id_journal`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `final_evaluation`
--
ALTER TABLE `final_evaluation`
  ADD PRIMARY KEY (`id_evaluation`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `internship_placement`
--
ALTER TABLE `internship_placement`
  ADD PRIMARY KEY (`id_placement`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_company` (`id_company`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id_profile`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id_report`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD PRIMARY KEY (`id_role_permission`),
  ADD KEY `id_role` (`id_role`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users_role`
--
ALTER TABLE `users_role`
  ADD PRIMARY KEY (`id_user_role`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_role` (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id_attendance` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendance_validation`
--
ALTER TABLE `attendance_validation`
  MODIFY `id_validation` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id_company` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `daily_journal`
--
ALTER TABLE `daily_journal`
  MODIFY `id_journal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `final_evaluation`
--
ALTER TABLE `final_evaluation`
  MODIFY `id_evaluation` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `internship_placement`
--
ALTER TABLE `internship_placement`
  MODIFY `id_placement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id_profile` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id_report` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  MODIFY `id_role_permission` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users_role`
--
ALTER TABLE `users_role`
  MODIFY `id_user_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `attendance_validation`
--
ALTER TABLE `attendance_validation`
  ADD CONSTRAINT `attendance_validation_ibfk_1` FOREIGN KEY (`id_attendance`) REFERENCES `attendances` (`id_attendance`),
  ADD CONSTRAINT `attendance_validation_ibfk_2` FOREIGN KEY (`id_pembimbing`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `daily_journal`
--
ALTER TABLE `daily_journal`
  ADD CONSTRAINT `daily_journal_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `final_evaluation`
--
ALTER TABLE `final_evaluation`
  ADD CONSTRAINT `final_evaluation_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `internship_placement`
--
ALTER TABLE `internship_placement`
  ADD CONSTRAINT `internship_placement_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `internship_placement_ibfk_2` FOREIGN KEY (`id_company`) REFERENCES `company` (`id_company`);

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD CONSTRAINT `roles_permissions_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`);

--
-- Constraints for table `users_role`
--
ALTER TABLE `users_role`
  ADD CONSTRAINT `users_role_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `users_role_ibfk_2` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
