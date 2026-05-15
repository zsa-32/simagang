-- Migration: Create leave_requests table for student leave request approval system
-- Run this SQL in your simagang database

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL COMMENT 'sakit, keperluan_kampus, keperluan_keluarga, lainnya',
  `dari_tanggal` date NOT NULL,
  `sampai_tanggal` date NOT NULL,
  `alasan` text NOT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending' COMMENT 'pending, approved, ditolak',
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'user_id pembimbing yang review',
  `reviewed_at` datetime DEFAULT NULL,
  `catatan_reviewer` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `mahasiswa_id` (`mahasiswa_id`),
  KEY `reviewed_by` (`reviewed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
