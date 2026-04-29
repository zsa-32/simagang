-- 1. Tabel roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- 3. Tabel log_logins
CREATE TABLE log_logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255),
    status VARCHAR(20),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    device_info TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 7. Tabel companies
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_perusahaan VARCHAR(255) NOT NULL,
    alamat_perusahaan TEXT,
    email_business VARCHAR(255) UNIQUE NOT NULL,
    contact_person VARCHAR(100),
    no_hp VARCHAR(20),
    bidang_usaha VARCHAR(100),
    status_permodalan VARCHAR(100),
    visi TEXT,
    misi TEXT,
    denah_lokasi TEXT,
    deskripsi_kerja TEXT,
    struktur_organisasi TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    radius INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Tabel pembimbing_lapang
CREATE TABLE pembimbing_lapang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    company_id INT,
    nama VARCHAR(255) NOT NULL,
    jabatan VARCHAR(100),
    no_hp VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- 9. Tabel dosen_pembimbing
CREATE TABLE dosen_pembimbing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nama VARCHAR(255) NOT NULL,
    nip VARCHAR(50) UNIQUE NOT NULL,
    jenis_kelamin VARCHAR(20),
    no_hp VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Tabel groups
CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    company_id INT,
    pembimbing_lapang_id INT,
    dosen_pembimbing_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    FOREIGN KEY (pembimbing_lapang_id) REFERENCES pembimbing_lapang(id) ON DELETE SET NULL,
    FOREIGN KEY (dosen_pembimbing_id) REFERENCES dosen_pembimbing(id) ON DELETE SET NULL
);

-- 4. Tabel mahasiswa
CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    group_id INT,
    nama VARCHAR(255) NOT NULL,
    jenis_kelamin VARCHAR(20),
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    agama VARCHAR(50),
    status VARCHAR(50),
    alamat_asal TEXT,
    alamat_jember TEXT,
    no_ktp VARCHAR(20),
    no_ktm VARCHAR(20),
    no_hp VARCHAR(20),
    golongan_darah VARCHAR(5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
);

-- 5. Tabel kontak_darurat
CREATE TABLE kontak_darurat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    nama VARCHAR(255) NOT NULL,
    hubungan VARCHAR(50),
    no_telepon VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
);

-- 10. Tabel koordinator
CREATE TABLE koordinator (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nama VARCHAR(255) NOT NULL,
    nip VARCHAR(50) UNIQUE NOT NULL,
    jenis_kelamin VARCHAR(20),
    no_hp VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 11. Tabel attendances
CREATE TABLE attendances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    date DATE NOT NULL,
    checkin_time TIME,
    status VARCHAR(20),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
);

-- 12. Tabel attendance_logs
CREATE TABLE attendance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    action VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    device_info TEXT,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
);

-- 13. Tabel logbooks
CREATE TABLE logbooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    tanggal DATE NOT NULL,
    kegiatan TEXT,
    hasil TEXT,
    kendala TEXT,
    solusi TEXT,
    dokumentasi VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
);

-- 14. Tabel feedback_logbooks
CREATE TABLE feedback_logbooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logbook_id INT,
    penilai_user_id INT,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
    FOREIGN KEY (penilai_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 15. Tabel log_logbooks
CREATE TABLE log_logbooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logbook_id INT,
    action VARCHAR(50),
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE
);

-- 16. Tabel projects
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    dosen_pembimbing_id INT,
    nama_project VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (dosen_pembimbing_id) REFERENCES dosen_pembimbing(id) ON DELETE SET NULL
);

-- 17. Tabel project_feedbacks
CREATE TABLE project_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    penilai_user_id INT,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (penilai_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 18. Tabel final_reports
CREATE TABLE final_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    mahasiswa_id INT,
    judul_laporan VARCHAR(255) NOT NULL,
    ringkasan TEXT,
    file_path VARCHAR(255),
    status VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
);

-- 19. Tabel final_report_feedbacks
CREATE TABLE final_report_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    final_report_id INT,
    penilai_user_id INT,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (final_report_id) REFERENCES final_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (penilai_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 20. Tabel log_final_reports
CREATE TABLE log_final_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    final_report_id INT,
    file_path VARCHAR(255),
    status VARCHAR(20),
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (final_report_id) REFERENCES final_reports(id) ON DELETE CASCADE
);

-- 21. Tabel komponen_penilaian
CREATE TABLE komponen_penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    bobot_persen INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 22. Tabel kriteria_penilaian
CREATE TABLE kriteria_penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    komponen_id INT,
    nama VARCHAR(255) NOT NULL,
    bobot INT,
    nilai_max INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (komponen_id) REFERENCES komponen_penilaian(id) ON DELETE CASCADE
);

-- 23. Tabel nilai_kriteria
CREATE TABLE nilai_kriteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    kriteria_id INT,
    penilai_user_id INT,
    nilai_angka DECIMAL(5, 2),
    nilai_huruf VARCHAR(5),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (kriteria_id) REFERENCES kriteria_penilaian(id) ON DELETE CASCADE,
    FOREIGN KEY (penilai_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 24. Tabel penilaian_results
CREATE TABLE penilaian_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    nilai_akhir DECIMAL(5, 2),
    grade VARCHAR(5),
    status_publish BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
);

-- 25. Tabel notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 26. Tabel settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kampus VARCHAR(255),
    tahun_ajaran VARCHAR(20),
    jam_absen_masuk TIME,
    toleransi_telat INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);