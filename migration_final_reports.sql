-- ============================================================
-- SIMAGANG — Migration: Tambah kolom mahasiswa_id di final_reports
-- Jalankan script ini SATU KALI di phpMyAdmin
-- ============================================================

-- Tambah kolom mahasiswa_id ke tabel final_reports
ALTER TABLE final_reports 
    ADD COLUMN mahasiswa_id INT NULL AFTER id;

-- Tambah foreign key
ALTER TABLE final_reports
    ADD CONSTRAINT fk_final_reports_mhs
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE;

-- Verifikasi (opsional, jalankan untuk cek):
-- DESCRIBE final_reports;
