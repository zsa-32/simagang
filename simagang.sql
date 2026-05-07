-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 07, 2026 at 03:24 PM
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
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `checkin_time` time DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `nama_perusahaan` varchar(255) NOT NULL,
  `alamat_perusahaan` text,
  `email_business` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `bidang_usaha` varchar(100) DEFAULT NULL,
  `status_permodalan` varchar(100) DEFAULT NULL,
  `visi` text,
  `misi` text,
  `denah_lokasi` text,
  `deskripsi_kerja` text,
  `struktur_organisasi` text,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `radius` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `nama_perusahaan`, `alamat_perusahaan`, `email_business`, `contact_person`, `no_hp`, `bidang_usaha`, `status_permodalan`, `visi`, `misi`, `denah_lokasi`, `deskripsi_kerja`, `struktur_organisasi`, `latitude`, `longitude`, `radius`, `created_at`, `updated_at`) VALUES
(1, 'PT Telkom Indonesia', 'Jl. Japati No. 1, Bandung', 'hr@telkom.co.id', 'Agus Setiawan', '02122471000', 'Telekomunikasi', NULL, NULL, NULL, NULL, NULL, NULL, '-6.91750000', '107.61910000', 200, '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `dosen_pembimbing`
--

CREATE TABLE `dosen_pembimbing` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dosen_pembimbing`
--

INSERT INTO `dosen_pembimbing` (`id`, `user_id`, `nama`, `nip`, `jenis_kelamin`, `no_hp`, `email`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 2, 'Dr. Budi Santoso, M.Kom', '198501012010011001', 'Laki-laki', '08123456780', 'budi.santoso@polije.ac.id', 'Jl. Mastrip, Jember', '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_logbooks`
--

CREATE TABLE `feedback_logbooks` (
  `id` int(11) NOT NULL,
  `logbook_id` int(11) DEFAULT NULL,
  `penilai_user_id` int(11) DEFAULT NULL,
  `feedback` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `final_reports`
--

CREATE TABLE `final_reports` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `ringkasan` text,
  `file_path` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `final_reports`
--

INSERT INTO `final_reports` (`id`, `project_id`, `mahasiswa_id`, `judul_laporan`, `ringkasan`, `file_path`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, '11. Penulisan Daftar Pustaka APA.pdf', NULL, 'uploads/reports/report_1_1778162763.pdf', 'pending', '2026-05-07 14:06:03', '2026-05-07 14:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `final_report_feedbacks`
--

CREATE TABLE `final_report_feedbacks` (
  `id` int(11) NOT NULL,
  `final_report_id` int(11) DEFAULT NULL,
  `penilai_user_id` int(11) DEFAULT NULL,
  `feedback` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `pembimbing_lapang_id` int(11) DEFAULT NULL,
  `dosen_pembimbing_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `company_id`, `pembimbing_lapang_id`, `dosen_pembimbing_id`, `created_at`, `updated_at`) VALUES
(1, 'Kelompok Magang Telkom 2026', 1, 1, 1, '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `komponen_penilaian`
--

CREATE TABLE `komponen_penilaian` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `bobot_persen` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `komponen_penilaian`
--

INSERT INTO `komponen_penilaian` (`id`, `nama`, `bobot_persen`, `created_at`, `updated_at`) VALUES
(1, 'Kehadiran', 15, '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(2, 'Teknis', 40, '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(3, 'Soft Skill', 20, '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(4, 'Laporan Akhir', 25, '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `kontak_darurat`
--

CREATE TABLE `kontak_darurat` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `hubungan` varchar(50) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `koordinator`
--

CREATE TABLE `koordinator` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `koordinator`
--

INSERT INTO `koordinator` (`id`, `user_id`, `nama`, `nip`, `jenis_kelamin`, `no_hp`, `email`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrator', '000000000000000000', 'Laki-laki', '08123456789', 'admin@polije.ac.id', 'Kampus Polije', '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `kriteria_penilaian`
--

CREATE TABLE `kriteria_penilaian` (
  `id` int(11) NOT NULL,
  `komponen_id` int(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `bobot` int(11) DEFAULT NULL,
  `nilai_max` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `kriteria_penilaian`
--

INSERT INTO `kriteria_penilaian` (`id`, `komponen_id`, `nama`, `bobot`, `nilai_max`, `created_at`, `updated_at`) VALUES
(1, 1, 'Presensi dan partisipasi aktif', 100, 100, '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(2, 2, 'Kemampuan teknis dan coding', 100, 100, '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(3, 3, 'Komunikasi dan teamwork', 100, 100, '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(4, 4, 'Kualitas laporan akhir', 50, 100, '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(5, 4, 'Seminar / Presentasi', 50, 100, '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `logbooks`
--

CREATE TABLE `logbooks` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `kegiatan` text,
  `hasil` text,
  `kendala` text,
  `solusi` text,
  `dokumentasi` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `log_logins`
--

CREATE TABLE `log_logins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_info` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `log_logins`
--

INSERT INTO `log_logins` (`id`, `user_id`, `email`, `status`, `timestamp`, `ip_address`, `device_info`) VALUES
(1, 4, 'ahmad.rizki@student.polije.ac.id', 'success', '2026-05-07 13:53:36', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36'),
(2, 4, 'ahmad.rizki@student.polije.ac.id', 'success', '2026-05-07 14:01:05', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36'),
(3, 4, 'ahmad.rizki@student.polije.ac.id', 'success', '2026-05-07 15:18:18', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36'),
(4, 4, 'ahmad.rizki@student.polije.ac.id', 'success', '2026-05-07 15:19:02', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `alamat_asal` text,
  `alamat_jember` text,
  `no_ktp` varchar(20) DEFAULT NULL,
  `no_ktm` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `golongan_darah` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `user_id`, `group_id`, `nama`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `agama`, `status`, `alamat_asal`, `alamat_jember`, `no_ktp`, `no_ktm`, `no_hp`, `golongan_darah`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'Ahmad Rizki', 'Laki-laki', 'Jember', '2003-05-15', 'Islam', 'Aktif', NULL, NULL, NULL, NULL, '08123456781', NULL, '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `nilai_kriteria`
--

CREATE TABLE `nilai_kriteria` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `kriteria_id` int(11) DEFAULT NULL,
  `penilai_user_id` int(11) DEFAULT NULL,
  `nilai_angka` decimal(5,2) DEFAULT NULL,
  `nilai_huruf` varchar(5) DEFAULT NULL,
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pembimbing_lapang`
--

CREATE TABLE `pembimbing_lapang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pembimbing_lapang`
--

INSERT INTO `pembimbing_lapang` (`id`, `user_id`, `company_id`, `nama`, `jabatan`, `no_hp`, `email`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Ir. Agus Setiawan', 'Senior Engineer', '08129876543', 'agus.setiawan@telkom.co.id', '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_results`
--

CREATE TABLE `penilaian_results` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `status_publish` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `dosen_pembimbing_id` int(11) DEFAULT NULL,
  `nama_project` varchar(255) NOT NULL,
  `deskripsi` text,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `project_feedbacks`
--

CREATE TABLE `project_feedbacks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `penilai_user_id` int(11) DEFAULT NULL,
  `feedback` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'admin', '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(2, 'mahasiswa', '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(3, 'dosen_pembimbing', '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(4, 'pembimbing_lapang', '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(5, 'koordinator', '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `nama_kampus` varchar(255) DEFAULT NULL,
  `tahun_ajaran` varchar(20) DEFAULT NULL,
  `jam_absen_masuk` time DEFAULT NULL,
  `toleransi_telat` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `nama_kampus`, `tahun_ajaran`, `jam_absen_masuk`, `toleransi_telat`, `created_at`, `updated_at`) VALUES
(1, 'Politeknik Negeri Jember', '2025/2026', '08:00:00', 15, '2026-05-07 13:52:55', '2026-05-07 13:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrator', 'admin@polije.ac.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.', '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(2, 3, 'Dr. Budi Santoso, M.Kom', 'budi.santoso@polije.ac.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.', '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(3, 4, 'Ir. Agus Setiawan', 'agus.setiawan@telkom.co.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.', '2026-05-07 13:52:55', '2026-05-07 13:52:55'),
(4, 2, 'Ahmad Rizki', 'ahmad.rizki@student.polije.ac.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.', '2026-05-07 13:52:55', '2026-05-07 13:52:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_business` (`email_business`);

--
-- Indexes for table `dosen_pembimbing`
--
ALTER TABLE `dosen_pembimbing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback_logbooks`
--
ALTER TABLE `feedback_logbooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logbook_id` (`logbook_id`),
  ADD KEY `penilai_user_id` (`penilai_user_id`);

--
-- Indexes for table `final_reports`
--
ALTER TABLE `final_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `final_reports_ibfk_2` (`mahasiswa_id`);

--
-- Indexes for table `final_report_feedbacks`
--
ALTER TABLE `final_report_feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `final_report_id` (`final_report_id`),
  ADD KEY `penilai_user_id` (`penilai_user_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `pembimbing_lapang_id` (`pembimbing_lapang_id`),
  ADD KEY `dosen_pembimbing_id` (`dosen_pembimbing_id`);

--
-- Indexes for table `komponen_penilaian`
--
ALTER TABLE `komponen_penilaian`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kontak_darurat`
--
ALTER TABLE `kontak_darurat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `koordinator`
--
ALTER TABLE `koordinator`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `kriteria_penilaian`
--
ALTER TABLE `kriteria_penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `komponen_id` (`komponen_id`);

--
-- Indexes for table `logbooks`
--
ALTER TABLE `logbooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `log_logins`
--
ALTER TABLE `log_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `nilai_kriteria`
--
ALTER TABLE `nilai_kriteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `kriteria_id` (`kriteria_id`),
  ADD KEY `penilai_user_id` (`penilai_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pembimbing_lapang`
--
ALTER TABLE `pembimbing_lapang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `penilaian_results`
--
ALTER TABLE `penilaian_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `dosen_pembimbing_id` (`dosen_pembimbing_id`);

--
-- Indexes for table `project_feedbacks`
--
ALTER TABLE `project_feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `penilai_user_id` (`penilai_user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dosen_pembimbing`
--
ALTER TABLE `dosen_pembimbing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback_logbooks`
--
ALTER TABLE `feedback_logbooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_reports`
--
ALTER TABLE `final_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `final_report_feedbacks`
--
ALTER TABLE `final_report_feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `komponen_penilaian`
--
ALTER TABLE `komponen_penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kontak_darurat`
--
ALTER TABLE `kontak_darurat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `koordinator`
--
ALTER TABLE `koordinator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kriteria_penilaian`
--
ALTER TABLE `kriteria_penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `logbooks`
--
ALTER TABLE `logbooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_logins`
--
ALTER TABLE `log_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nilai_kriteria`
--
ALTER TABLE `nilai_kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembimbing_lapang`
--
ALTER TABLE `pembimbing_lapang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penilaian_results`
--
ALTER TABLE `penilaian_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_feedbacks`
--
ALTER TABLE `project_feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dosen_pembimbing`
--
ALTER TABLE `dosen_pembimbing`
  ADD CONSTRAINT `dosen_pembimbing_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_logbooks`
--
ALTER TABLE `feedback_logbooks`
  ADD CONSTRAINT `feedback_logbooks_ibfk_1` FOREIGN KEY (`logbook_id`) REFERENCES `logbooks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_logbooks_ibfk_2` FOREIGN KEY (`penilai_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `final_reports`
--
ALTER TABLE `final_reports`
  ADD CONSTRAINT `final_reports_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `final_reports_ibfk_2` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `final_report_feedbacks`
--
ALTER TABLE `final_report_feedbacks`
  ADD CONSTRAINT `final_report_feedbacks_ibfk_1` FOREIGN KEY (`final_report_id`) REFERENCES `final_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `final_report_feedbacks_ibfk_2` FOREIGN KEY (`penilai_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `groups_ibfk_2` FOREIGN KEY (`pembimbing_lapang_id`) REFERENCES `pembimbing_lapang` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `groups_ibfk_3` FOREIGN KEY (`dosen_pembimbing_id`) REFERENCES `dosen_pembimbing` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kontak_darurat`
--
ALTER TABLE `kontak_darurat`
  ADD CONSTRAINT `kontak_darurat_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `koordinator`
--
ALTER TABLE `koordinator`
  ADD CONSTRAINT `koordinator_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kriteria_penilaian`
--
ALTER TABLE `kriteria_penilaian`
  ADD CONSTRAINT `kriteria_penilaian_ibfk_1` FOREIGN KEY (`komponen_id`) REFERENCES `komponen_penilaian` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logbooks`
--
ALTER TABLE `logbooks`
  ADD CONSTRAINT `logbooks_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_logins`
--
ALTER TABLE `log_logins`
  ADD CONSTRAINT `log_logins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `nilai_kriteria`
--
ALTER TABLE `nilai_kriteria`
  ADD CONSTRAINT `nilai_kriteria_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_kriteria_ibfk_2` FOREIGN KEY (`kriteria_id`) REFERENCES `kriteria_penilaian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_kriteria_ibfk_3` FOREIGN KEY (`penilai_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pembimbing_lapang`
--
ALTER TABLE `pembimbing_lapang`
  ADD CONSTRAINT `pembimbing_lapang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembimbing_lapang_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `penilaian_results`
--
ALTER TABLE `penilaian_results`
  ADD CONSTRAINT `penilaian_results_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`dosen_pembimbing_id`) REFERENCES `dosen_pembimbing` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_feedbacks`
--
ALTER TABLE `project_feedbacks`
  ADD CONSTRAINT `project_feedbacks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_feedbacks_ibfk_2` FOREIGN KEY (`penilai_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
