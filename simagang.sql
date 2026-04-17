-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 16, 2026 at 04:47 PM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

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
-- Table structure for table `Attendances`
--

CREATE TABLE `Attendances` (
  `id_attendance` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_masuk` time DEFAULT NULL,
  `waktu_keluar` time DEFAULT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') DEFAULT NULL,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Attendances`
--

INSERT INTO `Attendances` (`id_attendance`, `id_user`, `tanggal`, `waktu_masuk`, `waktu_keluar`, `status`, `keterangan`) VALUES
(1, 4, '2026-04-16', '08:15:00', '17:00:00', 'Hadir', 'Tugas di kantor pusat');

-- --------------------------------------------------------

--
-- Table structure for table `Attendance_validation`
--

CREATE TABLE `Attendance_validation` (
  `id_validation` int(11) NOT NULL,
  `id_attendance` int(11) DEFAULT NULL,
  `id_pembimbing` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Attendance_validation`
--

INSERT INTO `Attendance_validation` (`id_validation`, `id_attendance`, `id_pembimbing`, `status`) VALUES
(1, 1, 3, 'Disetujui Pembimbing Lapang');

-- --------------------------------------------------------

--
-- Table structure for table `Company`
--

CREATE TABLE `Company` (
  `id_company` int(11) NOT NULL,
  `nama_company` varchar(255) NOT NULL,
  `alamat` text,
  `kontak` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Company`
--

INSERT INTO `Company` (`id_company`, `nama_company`, `alamat`, `kontak`) VALUES
(1, 'PT. Inovasi Digital', 'Cyber Tower Lt. 10, Jakarta', '021-998877');

-- --------------------------------------------------------

--
-- Table structure for table `Daily_journal`
--

CREATE TABLE `Daily_journal` (
  `id_journal` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `kegiatan` text,
  `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `catatan_dosen` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Daily_journal`
--

INSERT INTO `Daily_journal` (`id_journal`, `id_user`, `tanggal`, `kegiatan`, `status`, `catatan_dosen`) VALUES
(1, 4, '2026-04-16', 'Melakukan testing pada modul autentikasi user.', 'Disetujui', 'Bagus, lanjutkan!');


-- --------------------------------------------------------

--
-- Table structure for table `Journal_validation`
--

CREATE TABLE `Journal_validation` (
  `id_jval` int(11) NOT NULL,
  `id_journal` int(11) DEFAULT NULL,
  `id_validator` int(11) DEFAULT NULL,
  `status` enum('Disetujui','Ditolak') DEFAULT NULL,
  `catatan` text,
  `validated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `Final_evaluation`
--

CREATE TABLE `Final_evaluation` (
  `id_evaluation` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `komentar` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Final_evaluation`
--

INSERT INTO `Final_evaluation` (`id_evaluation`, `id_user`, `nilai_akhir`, `komentar`) VALUES
(1, 4, '88.00', 'Performa sangat baik di lapangan dan laporan tersusun rapi.');

-- --------------------------------------------------------

--
-- Table structure for table `Internship_placement`
--

CREATE TABLE `Internship_placement` (
  `id_placement` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_company` int(11) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Internship_placement`
--

INSERT INTO `Internship_placement` (`id_placement`, `id_user`, `id_company`, `tanggal_mulai`, `tanggal_selesai`) VALUES
(1, 4, 1, '2026-03-01', '2026-08-31');

-- --------------------------------------------------------

--
-- Table structure for table `Profile`
--

CREATE TABLE `Profile` (
  `id_profile` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `prodi` varchar(100) DEFAULT NULL,
  `semester` int(2) DEFAULT NULL,
  `nama_perusahaan` varchar(150) DEFAULT NULL,
  `posisi_magang` varchar(100) DEFAULT NULL,
  `dosen_pembimbing` varchar(150) DEFAULT NULL,
  `id_dosen_pembimbing` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Profile`
--

INSERT INTO `Profile` (`id_profile`, `id_user`, `nip`, `nim`, `no_hp`, `alamat`, `prodi`, `semester`, `nama_perusahaan`, `dosen_pembimbing`, `id_dosen_pembimbing`) VALUES
(1, 4, NULL, '21000123', '081234567890', 'Jl. Teknik No. 5, Surabaya', NULL, NULL, NULL, NULL, NULL),
(2, NULL, '12345678', NULL, '0987654321', 'jalan karimata', NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Reports`
--

CREATE TABLE `Reports` (
  `id_report` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `jenis_laporan` varchar(100) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE `Roles` (
  `id_role` int(11) NOT NULL,
  `nama_role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Roles`
--

INSERT INTO `Roles` (`id_role`, `nama_role`) VALUES
(1, 'Admin'),
(2, 'Dosen Pembimbing'),
(3, 'Pembimbing Lapang'),
(4, 'Mahasiswa');

-- --------------------------------------------------------

--
-- Table structure for table `Roles_permissions`
--

CREATE TABLE `Roles_permissions` (
  `id_role_permission` int(11) NOT NULL,
  `id_role` int(11) DEFAULT NULL,
  `permission` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id_user`, `nama`, `email`, `password`) VALUES
(1, 'Sistem Admin', 'admin@simagang.com', 'admin123'),
(2, 'Dr. Aris Sudarsono', 'aris@univ.ac.id', 'dosen123'),
(3, 'Bapak Eko Tech', 'eko@company.com', 'lapang123'),
(4, 'Rizky Pratama', 'rizky@student.com', 'mhs123');

-- --------------------------------------------------------

--
-- Table structure for table `Users_role`
--

CREATE TABLE `Users_role` (
  `id_user_role` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_role` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Users_role`
--

INSERT INTO `Users_role` (`id_user_role`, `id_user`, `id_role`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Attendances`
--
ALTER TABLE `Attendances`
  ADD PRIMARY KEY (`id_attendance`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `Attendance_validation`
--
ALTER TABLE `Attendance_validation`
  ADD PRIMARY KEY (`id_validation`),
  ADD KEY `id_attendance` (`id_attendance`),
  ADD KEY `id_pembimbing` (`id_pembimbing`);

--
-- Indexes for table `Company`
--
ALTER TABLE `Company`
  ADD PRIMARY KEY (`id_company`);

--
-- Indexes for table `Daily_journal`
--
ALTER TABLE `Daily_journal`
  ADD PRIMARY KEY (`id_journal`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `Final_evaluation`
--
ALTER TABLE `Final_evaluation`
  ADD PRIMARY KEY (`id_evaluation`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `Internship_placement`
--
ALTER TABLE `Internship_placement`
  ADD PRIMARY KEY (`id_placement`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_company` (`id_company`);

--
-- Indexes for table `Profile`
--
ALTER TABLE `Profile`
  ADD PRIMARY KEY (`id_profile`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `Reports`
--
ALTER TABLE `Reports`
  ADD PRIMARY KEY (`id_report`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `Roles_permissions`
--
ALTER TABLE `Roles_permissions`
  ADD PRIMARY KEY (`id_role_permission`),
  ADD KEY `id_role` (`id_role`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `Users_role`
--
ALTER TABLE `Users_role`
  ADD PRIMARY KEY (`id_user_role`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_role` (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Attendances`
--
ALTER TABLE `Attendances`
  MODIFY `id_attendance` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Attendance_validation`
--
ALTER TABLE `Attendance_validation`
  MODIFY `id_validation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Company`
--
ALTER TABLE `Company`
  MODIFY `id_company` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Daily_journal`
--
ALTER TABLE `Daily_journal`
  MODIFY `id_journal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Final_evaluation`
--
ALTER TABLE `Final_evaluation`
  MODIFY `id_evaluation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Internship_placement`
--
ALTER TABLE `Internship_placement`
  MODIFY `id_placement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Profile`
--
ALTER TABLE `Profile`
  MODIFY `id_profile` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Reports`
--
ALTER TABLE `Reports`
  MODIFY `id_report` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Roles_permissions`
--
ALTER TABLE `Roles_permissions`
  MODIFY `id_role_permission` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Users_role`
--
ALTER TABLE `Users_role`
  MODIFY `id_user_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Attendances`
--
ALTER TABLE `Attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`);

--
-- Constraints for table `Attendance_validation`
--
ALTER TABLE `Attendance_validation`
  ADD CONSTRAINT `attendance_validation_ibfk_1` FOREIGN KEY (`id_attendance`) REFERENCES `Attendances` (`id_attendance`),
  ADD CONSTRAINT `attendance_validation_ibfk_2` FOREIGN KEY (`id_pembimbing`) REFERENCES `Users` (`id_user`);

--
-- Constraints for table `Daily_journal`
--
ALTER TABLE `Daily_journal`
  ADD CONSTRAINT `daily_journal_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`);

--
-- Constraints for table `Final_evaluation`
--
ALTER TABLE `Final_evaluation`
  ADD CONSTRAINT `final_evaluation_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`);

--
-- Constraints for table `Internship_placement`
--
ALTER TABLE `Internship_placement`
  ADD CONSTRAINT `internship_placement_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`),
  ADD CONSTRAINT `internship_placement_ibfk_2` FOREIGN KEY (`id_company`) REFERENCES `Company` (`id_company`);

--
-- Constraints for table `Profile`
--
ALTER TABLE `Profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`);

--
-- Constraints for table `Reports`
--
ALTER TABLE `Reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`);

--
-- Constraints for table `Roles_permissions`
--
ALTER TABLE `Roles_permissions`
  ADD CONSTRAINT `roles_permissions_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `Roles` (`id_role`);

--
-- Constraints for table `Users_role`
--
ALTER TABLE `Users_role`
  ADD CONSTRAINT `users_role_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`),
  ADD CONSTRAINT `users_role_ibfk_2` FOREIGN KEY (`id_role`) REFERENCES `Roles` (`id_role`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
