-- =====================================================
-- SIMAGANG SEED DATA
-- Run this AFTER simagang.sql to populate defaults
-- =====================================================

-- 1. Default Roles
INSERT INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'mahasiswa'),
(3, 'dosen_pembimbing'),
(4, 'pembimbing_lapang');

-- 2. Default Admin User
-- Password: admin123 (bcrypt hashed)
INSERT INTO users (id, role_id, name, email, password) VALUES
(1, 1, 'Administrator', 'admin@polije.ac.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.');

-- 3. Default Koordinator record for admin
INSERT INTO koordinator (id, user_id, nama, nip, jenis_kelamin, no_hp, email, alamat) VALUES
(1, 1, 'Administrator', '000000000000000000', 'Laki-laki', '08123456789', 'admin@polije.ac.id', 'Kampus Polije');

-- 4. Default System Settings
INSERT INTO settings (id, nama_kampus, tahun_ajaran, jam_absen_masuk, toleransi_telat) VALUES
(1, 'Politeknik Negeri Jember', '2025/2026', '08:00:00', 15);

-- 5. Default Komponen Penilaian
INSERT INTO komponen_penilaian (id, nama, bobot_persen) VALUES
(1, 'Kehadiran', 15),
(2, 'Teknis', 40),
(3, 'Soft Skill', 20),
(4, 'Laporan Akhir', 25);

-- 6. Default Kriteria Penilaian
INSERT INTO kriteria_penilaian (id, komponen_id, nama, bobot, nilai_max) VALUES
(1, 1, 'Presensi dan partisipasi aktif', 100, 100),
(2, 2, 'Kemampuan teknis dan coding', 100, 100),
(3, 3, 'Komunikasi dan teamwork', 100, 100),
(4, 4, 'Kualitas laporan akhir', 50, 100),
(5, 4, 'Seminar / Presentasi', 50, 100);

-- 7. Sample Dosen Pembimbing
INSERT INTO users (id, role_id, name, email, password) VALUES
(2, 3, 'Dr. Budi Santoso, M.Kom', 'budi.santoso@polije.ac.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.');

INSERT INTO dosen_pembimbing (id, user_id, nama, nip, jenis_kelamin, no_hp, email, alamat) VALUES
(1, 2, 'Dr. Budi Santoso, M.Kom', '198501012010011001', 'Laki-laki', '08123456780', 'budi.santoso@polije.ac.id', 'Jl. Mastrip, Jember');

-- 8. Sample Company
INSERT INTO companies (id, nama_perusahaan, alamat_perusahaan, email_business, contact_person, no_hp, bidang_usaha, latitude, longitude, radius) VALUES
(1, 'PT Telkom Indonesia', 'Jl. Japati No. 1, Bandung', 'hr@telkom.co.id', 'Agus Setiawan', '02122471000', 'Telekomunikasi', -6.9175, 107.6191, 200);

-- 9. Sample Pembimbing Lapang
INSERT INTO users (id, role_id, name, email, password) VALUES
(3, 4, 'Ir. Agus Setiawan', 'agus.setiawan@telkom.co.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.');

INSERT INTO pembimbing_lapang (id, user_id, company_id, nama, jabatan, no_hp, email) VALUES
(1, 3, 1, 'Ir. Agus Setiawan', 'Senior Engineer', '08129876543', 'agus.setiawan@telkom.co.id');

-- 10. Sample Group
INSERT INTO `groups` (id, name, company_id, pembimbing_lapang_id, dosen_pembimbing_id) VALUES
(1, 'Kelompok Magang Telkom 2026', 1, 1, 1);

-- 11. Sample Mahasiswa
INSERT INTO users (id, role_id, name, email, password) VALUES
(4, 2, 'Ahmad Rizki', 'ahmad.rizki@student.polije.ac.id', '$2y$10$JnE732zbKC63fxqKdqcWuuZII2iTqRpSofMUBPiLb7ofSarSTCwf.');

INSERT INTO mahasiswa (id, user_id, group_id, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, status, no_hp) VALUES
(1, 4, 1, 'Ahmad Rizki', 'Laki-laki', 'Jember', '2003-05-15', 'Islam', 'Aktif', '08123456781');
