-- =====================================================
-- MUNCAK.KUY - Database Schema
-- Website Booking Pendakian Gunung
-- =====================================================

CREATE DATABASE IF NOT EXISTS muncakkuy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE muncakkuy_db;

-- ================= TABEL USERS =================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(20) DEFAULT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================= TABEL GUNUNG =================
CREATE TABLE gunung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_gunung VARCHAR(100) NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    provinsi VARCHAR(100) NOT NULL,
    ketinggian INT NOT NULL COMMENT 'dalam mdpl',
    tingkat_kesulitan ENUM('Pemula','Menengah','Sulit','Ekstrem') NOT NULL DEFAULT 'Menengah',
    deskripsi TEXT,
    estimasi_waktu VARCHAR(50) COMMENT 'contoh: 2 Hari 1 Malam',
    harga_mulai DECIMAL(12,2) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    jumlah_review INT DEFAULT 0,
    foto_utama VARCHAR(255) DEFAULT NULL,
    status ENUM('buka','tutup') NOT NULL DEFAULT 'buka',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================= TABEL FOTO GUNUNG (galeri) =================
CREATE TABLE gunung_foto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gunung_id INT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    urutan INT DEFAULT 0,
    FOREIGN KEY (gunung_id) REFERENCES gunung(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= TABEL PAKET =================
CREATE TABLE paket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gunung_id INT NOT NULL,
    nama_paket VARCHAR(100) NOT NULL COMMENT 'Regular / Premium / VIP',
    harga DECIMAL(12,2) NOT NULL,
    fasilitas TEXT COMMENT 'JSON atau list dipisah koma',
    kuota_per_hari INT DEFAULT 20,
    FOREIGN KEY (gunung_id) REFERENCES gunung(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= TABEL BOOKING =================
CREATE TABLE booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_booking VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    gunung_id INT NOT NULL,
    paket_id INT NOT NULL,
    tanggal_pendakian DATE NOT NULL,
    jumlah_peserta INT NOT NULL DEFAULT 1,
    nama_peserta TEXT COMMENT 'JSON list nama peserta jika >1',
    total_harga DECIMAL(12,2) NOT NULL,
    metode_pembayaran VARCHAR(50) DEFAULT NULL,
    status_pembayaran ENUM('menunggu','lunas','gagal','refund') NOT NULL DEFAULT 'menunggu',
    status_pendakian ENUM('akan_datang','berlangsung','selesai','batal') NOT NULL DEFAULT 'akan_datang',
    qr_code_data VARCHAR(255) DEFAULT NULL COMMENT 'string unik untuk QR verifikasi',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (gunung_id) REFERENCES gunung(id),
    FOREIGN KEY (paket_id) REFERENCES paket(id)
) ENGINE=InnoDB;

-- ================= TABEL REVIEW =================
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    gunung_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (gunung_id) REFERENCES gunung(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= TABEL WISHLIST =================
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gunung_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, gunung_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (gunung_id) REFERENCES gunung(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= TABEL LOG AKTIVITAS (opsional untuk admin) =================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ================= DATA AWAL: ADMIN =================
-- password default: admin123 (HARUS diganti setelah instalasi!)
INSERT INTO users (nama_lengkap, email, password, role) VALUES
('Administrator', 'admin@muncakkuy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- hash di atas adalah bcrypt untuk "admin123"

-- ================= DATA AWAL: GUNUNG =================
INSERT INTO gunung (nama_gunung, lokasi, provinsi, ketinggian, tingkat_kesulitan, deskripsi, estimasi_waktu, harga_mulai, rating, jumlah_review, foto_utama) VALUES
('Gunung Rinjani', 'Lombok', 'Nusa Tenggara Barat', 3726, 'Sulit', 'Gunung berapi kedua tertinggi di Indonesia dengan danau kawah Segara Anak yang memukau.', '3 Hari 2 Malam', 650000, 4.9, 120, 'rinjani.jpg'),
('Gunung Semeru', 'Lumajang', 'Jawa Timur', 3676, 'Sulit', 'Puncak tertinggi di Pulau Jawa, dikenal dengan Mahameru dan padang savana Oro-oro Ombo.', '3 Hari 2 Malam', 500000, 4.8, 200, 'semeru.jpg'),
('Gunung Prau', 'Wonosobo', 'Jawa Tengah', 2565, 'Pemula', 'Terkenal dengan golden sunrise terbaik se-Asia Tenggara, cocok untuk pemula.', '1 Hari 1 Malam', 350000, 4.7, 210, 'prau.jpg'),
('Gunung Bromo', 'Probolinggo', 'Jawa Timur', 2329, 'Pemula', 'Ikon wisata gunung berapi aktif dengan lautan pasir dan matahari terbit legendaris.', '1 Hari 1 Malam', 450000, 4.8, 400, 'bromo.jpg');

-- ================= DATA AWAL: PAKET =================
INSERT INTO paket (gunung_id, nama_paket, harga, fasilitas, kuota_per_hari) VALUES
(1, 'Regular', 650000, 'Simaksi,Guide lokal,Tenda,P3K', 30),
(1, 'Premium', 1250000, 'Simaksi,Guide lokal,Tenda,P3K,Konsumsi,Porter', 20),
(2, 'Regular', 500000, 'Simaksi,Guide lokal,Tenda,P3K', 30),
(2, 'Premium', 950000, 'Simaksi,Guide lokal,Tenda,P3K,Konsumsi,Porter', 20),
(3, 'Regular', 350000, 'Simaksi,Guide lokal,P3K', 40),
(4, 'Regular', 450000, 'Simaksi,Jeep,Guide lokal', 40);
